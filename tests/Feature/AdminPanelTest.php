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
}
