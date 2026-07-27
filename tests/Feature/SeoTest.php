<?php

namespace Tests\Feature;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_robots_txt_disallows_private_paths_and_lists_sitemap(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /admin', false)
            ->assertSee('Sitemap:', false)
            ->assertSee('/sitemap.xml', false);
    }

    public function test_sitemap_includes_public_pages_and_books(): void
    {
        $book = Book::factory()->create([
            'title' => 'Seo Kitap',
            'author' => 'Yazar',
        ]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee(route('home'), false)
            ->assertSee(route('explore'), false)
            ->assertSee(route('books.show', $book), false);
    }

    public function test_book_page_has_seo_meta_and_json_ld(): void
    {
        $book = Book::factory()->create([
            'title' => '1984',
            'author' => 'George Orwell',
            'description' => 'Distopik bir roman.',
        ]);

        $this->get(route('books.show', $book))
            ->assertOk()
            ->assertSee('name="description"', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('"@type": "Book"', false)
            ->assertSee('1984', false);
    }

    public function test_numeric_book_url_redirects_to_slug(): void
    {
        $book = Book::factory()->create([
            'title' => 'Suç ve Ceza',
            'author' => 'Dostoyevski',
        ]);

        $this->get('/books/'.$book->id)
            ->assertRedirect(route('books.show', $book));
    }
}
