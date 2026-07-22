<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExploreFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_explore_can_filter_by_category(): void
    {
        $fiction = Category::create(['name' => 'Kurgu']);
        $science = Category::create(['name' => 'Bilim']);

        Book::factory()->create(['title' => 'Kurgu Kitabi', 'category_id' => $fiction->id]);
        Book::factory()->create(['title' => 'Bilim Kitabi', 'category_id' => $science->id]);

        $this->get(route('explore', ['category' => $fiction->id]))
            ->assertOk()
            ->assertSee('Kurgu Kitabi')
            ->assertDontSee('Bilim Kitabi');
    }

    public function test_explore_search_is_case_insensitive(): void
    {
        $category = Category::create(['name' => 'Bilim Kurgu']);
        Book::factory()->create(['title' => 'Dune', 'author' => 'Frank Herbert', 'category_id' => $category->id]);
        Book::factory()->create(['title' => '1984', 'author' => 'George Orwell', 'category_id' => $category->id]);

        $this->get(route('explore', ['q' => 'dune']))
            ->assertOk()
            ->assertSee('Dune')
            ->assertDontSee('1984');
    }

    public function test_user_profile_shows_shelves(): void
    {
        $user = User::factory()->create(['account_visibility' => User::VISIBILITY_PUBLIC]);
        $book = Book::factory()->create();
        $user->books()->attach($book->id, ['status' => 'okuyorum', 'is_protected' => true]);

        $this->get(route('users.show', $user))
            ->assertOk()
            ->assertSee('Okuyorum')
            ->assertSee($book->title);
    }
}
