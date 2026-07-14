<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_finds_books_by_title_prefix(): void
    {
        Book::factory()->create(['title' => 'Suc ve Ceza', 'author' => 'Dostoyevski']);
        Book::factory()->create(['title' => 'Kurk Mantolu Madonna', 'author' => 'Sabahattin Ali']);

        $this->get(route('search', ['q' => 'Su']))
            ->assertOk()
            ->assertSee('Suc ve Ceza')
            ->assertDontSee('Kurk Mantolu Madonna');
    }

    public function test_single_letter_prefix_search(): void
    {
        Book::factory()->create(['title' => 'Simyaci', 'author' => 'Paulo Coelho']);
        Book::factory()->create(['title' => '1984', 'author' => 'George Orwell']);

        $this->get(route('search', ['q' => 'S']))
            ->assertOk()
            ->assertSee('Simyaci')
            ->assertDontSee('1984');
    }

    public function test_search_finds_word_in_title(): void
    {
        Book::factory()->create(['title' => 'Suc ve Ceza', 'author' => 'Dostoyevski']);

        $this->get(route('search', ['q' => 'Ceza']))
            ->assertOk()
            ->assertSee('Suc ve Ceza');
    }

    public function test_fuzzy_search_finds_typo_in_title_word(): void
    {
        Book::factory()->create([
            'title' => 'Dogu Ekspresinde Cinayet',
            'author' => 'Agatha Christie',
        ]);
        Book::factory()->create(['title' => '1984', 'author' => 'George Orwell']);

        $this->get(route('search', ['q' => 'cinanyet']))
            ->assertOk()
            ->assertSee('Dogu Ekspresinde Cinayet')
            ->assertDontSee('1984');

        $this->getJson(route('search.suggest', ['q' => 'cinanyet']))
            ->assertOk()
            ->assertJsonPath('books.0.title', 'Dogu Ekspresinde Cinayet');
    }

    public function test_search_finds_public_users_by_name_prefix(): void
    {
        User::factory()->create([
            'name' => 'Ayse Kucuk',
            'account_visibility' => User::VISIBILITY_PUBLIC,
        ]);

        $this->get(route('search', ['q' => 'Ay']))
            ->assertOk()
            ->assertSee('Ayse Kucuk');
    }

    public function test_search_hides_followers_only_users_from_guests(): void
    {
        User::factory()->create([
            'name' => 'Gizli Kullanici',
            'account_visibility' => User::VISIBILITY_FOLLOWERS,
        ]);

        $this->get(route('search', ['q' => 'Giz']))
            ->assertOk()
            ->assertDontSee('Gizli Kullanici');
    }

    public function test_search_finds_posts_by_content_prefix(): void
    {
        $author = User::factory()->create(['account_visibility' => User::VISIBILITY_PUBLIC]);
        Post::factory()->for($author)->create(['content' => 'Klasik bir roman okudum']);

        $this->get(route('search', ['q' => 'Kla']))
            ->assertOk()
            ->assertSee('Klasik bir roman okudum');
    }

    public function test_empty_query_shows_hint(): void
    {
        $this->get(route('search'))
            ->assertOk()
            ->assertSee('Kitap, kullanıcı veya paylaşım ara');
    }

    public function test_navbar_includes_search_input(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Kitap, kullanıcı, paylaşım', false);
    }

    public function test_suggest_returns_matching_books_as_json(): void
    {
        Book::factory()->create(['title' => 'Sherlock Holmes', 'author' => 'Arthur Conan Doyle']);
        Book::factory()->create(['title' => '1984', 'author' => 'George Orwell']);

        $this->getJson(route('search.suggest', ['q' => 'Sher']))
            ->assertOk()
            ->assertJsonPath('books.0.title', 'Sherlock Holmes')
            ->assertJsonCount(1, 'books');
    }

    public function test_suggest_returns_empty_for_short_query(): void
    {
        $this->getJson(route('search.suggest', ['q' => '']))
            ->assertOk()
            ->assertJson(['books' => [], 'users' => [], 'posts' => []]);
    }
}
