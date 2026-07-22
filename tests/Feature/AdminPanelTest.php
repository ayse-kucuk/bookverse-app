<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_guest_cannot_access_admin(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_admin(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_view_dashboard(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Genel bakış');
    }

    public function test_admin_can_create_book(): void
    {
        $category = Category::create(['name' => 'Roman']);

        $this->actingAs($this->admin())
            ->post(route('admin.books.store'), [
                'title' => 'Yeni Roman',
                'author' => 'Yazar',
                'category_id' => $category->id,
                'image_url' => 'https://example.com/cover.jpg',
                'description' => 'Bu en az on karakterlik bir aciklama.',
                'page_count' => 220,
                'is_protected' => true,
            ])
            ->assertRedirect(route('admin.books.index'));

        $this->assertDatabaseHas('books', [
            'title' => 'Yeni Roman',
            'author' => 'Yazar',
        ]);
    }

    public function test_admin_cannot_create_duplicate_book_title(): void
    {
        $category = Category::create(['name' => 'Roman']);
        Book::factory()->create(['title' => 'Suç ve Ceza', 'category_id' => $category->id]);

        $this->actingAs($this->admin())
            ->from(route('admin.books.create'))
            ->post(route('admin.books.store'), [
                'title' => 'Suç ve Ceza',
                'author' => 'Başka Yazar',
                'category_id' => $category->id,
                'image_url' => 'https://example.com/cover.jpg',
                'description' => 'Bu en az on karakterlik bir aciklama.',
                'page_count' => 220,
            ])
            ->assertRedirect(route('admin.books.create'))
            ->assertSessionHasErrors(['title' => 'Bu kitap zaten eklenmiş.']);

        $this->assertDatabaseCount('books', 1);
    }

    public function test_admin_cannot_rename_book_to_existing_title(): void
    {
        $category = Category::create(['name' => 'Roman']);
        $existing = Book::factory()->create(['title' => '1984', 'category_id' => $category->id]);
        $book = Book::factory()->create(['title' => 'Hayvan Çiftliği', 'category_id' => $category->id]);

        $this->actingAs($this->admin())
            ->from(route('admin.books.edit', $book))
            ->put(route('admin.books.update', $book), [
                'title' => '1984',
                'author' => $book->author,
                'category_id' => $category->id,
                'image_url' => $book->image_url,
                'description' => $book->description,
                'page_count' => $book->page_count,
            ])
            ->assertRedirect(route('admin.books.edit', $book))
            ->assertSessionHasErrors(['title' => 'Bu kitap zaten eklenmiş.']);

        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'Hayvan Çiftliği']);
        $this->assertDatabaseHas('books', ['id' => $existing->id, 'title' => '1984']);
    }

    public function test_admin_can_delete_protected_book(): void
    {
        $book = Book::factory()->create(['is_protected' => true]);

        $this->actingAs($this->admin())
            ->delete(route('admin.books.destroy', $book))
            ->assertRedirect(route('admin.books.index'));

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_admin_can_create_category(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.categories.store'), [
                'name' => 'Bilim Kurgu',
                'description' => 'SF',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('categories', ['name' => 'Bilim Kurgu']);
    }

    public function test_admin_can_toggle_user_admin_role(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($admin)
            ->post(route('admin.users.toggle-admin', $user))
            ->assertRedirect();

        $this->assertTrue($user->fresh()->is_admin);
    }

    public function test_admin_cannot_remove_own_admin_role(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.users.toggle-admin', $admin))
            ->assertRedirect();

        $this->assertTrue($admin->fresh()->is_admin);
    }

    public function test_guest_cannot_search_google_books(): void
    {
        $this->getJson(route('admin.books.google-search', ['q' => 'dune']))
            ->assertUnauthorized();
    }

    public function test_non_admin_cannot_search_google_books(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->getJson(route('admin.books.google-search', ['q' => 'dune']))
            ->assertForbidden();
    }

    public function test_admin_can_search_google_books(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'www.googleapis.com/books/v1/volumes*' => \Illuminate\Support\Facades\Http::response([
                'items' => [
                    [
                        'id' => 'abc123',
                        'volumeInfo' => [
                            'title' => 'Dune',
                            'authors' => ['Frank Herbert'],
                            'description' => 'Çöl gezegeni Arrakis üzerine epik bir bilim kurgu romanı.',
                            'pageCount' => 688,
                            'publishedDate' => '1965-08-01',
                            'imageLinks' => [
                                'thumbnail' => 'http://books.google.com/thumb.jpg',
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $this->actingAs($this->admin())
            ->getJson(route('admin.books.google-search', ['q' => 'dune']))
            ->assertOk()
            ->assertJsonPath('results.0.title', 'Dune')
            ->assertJsonPath('results.0.author', 'Frank Herbert')
            ->assertJsonPath('results.0.page_count', 688)
            ->assertJsonPath('results.0.published_year', 1965);
    }

    public function test_google_books_search_requires_minimum_query_length(): void
    {
        $this->actingAs($this->admin())
            ->getJson(route('admin.books.google-search', ['q' => 'a']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['q']);
    }
}
