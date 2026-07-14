<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Services\GoogleBooksCoverResolver;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BookSeeder extends Seeder
{
    private const FALLBACK_BOOKS = [
        'Fantastik' => [
            ['title' => 'Yuzuklerin Efendisi: Yuzuk Kardesligi', 'author' => 'J.R.R. Tolkien'],
            ['title' => 'Harry Potter ve Felsefe Tasi', 'author' => 'J.K. Rowling'],
            ['title' => 'Hobbit', 'author' => 'J.R.R. Tolkien'],
        ],
        'Bilim Kurgu' => [
            ['title' => 'Dune', 'author' => 'Frank Herbert'],
            ['title' => 'Vakıf', 'author' => 'Isaac Asimov'],
            ['title' => 'Neuromancer', 'author' => 'William Gibson'],
        ],
        'Klasikler' => [
            ['title' => 'Suc ve Ceza', 'author' => 'Fyodor Dostoyevski'],
            ['title' => 'Sefiller', 'author' => 'Victor Hugo'],
            ['title' => 'Anna Karenina', 'author' => 'Lev Tolstoy'],
        ],
        'Polisiye' => [
            ['title' => 'Sherlock Holmes: Kizil Dosya', 'author' => 'Arthur Conan Doyle'],
            ['title' => 'Dogu Ekspresinde Cinayet', 'author' => 'Agatha Christie'],
            ['title' => 'Milenyum 1: Ejderha Dovmeli Kiz', 'author' => 'Stieg Larsson'],
        ],
    ];

    public function run(): void
    {
        $coverResolver = app(GoogleBooksCoverResolver::class);
        $apiKey = config('services.google_books.key');

        // Farklı kategorilerde arama yapalım
        $kategoriler = [
            'Fantastik' => 'fantasy',
            'Bilim Kurgu' => 'science+fiction',
            'Klasikler' => 'classics',
            'Polisiye' => 'mystery'
        ];

        foreach ($kategoriler as $kategoriIsmi => $apiQuery) {
            $kategori = Category::firstOrCreate(['name' => $kategoriIsmi]);
            $queryParams = [
                'q' => 'subject:' . $apiQuery,
                'langRestrict' => 'tr',
                'orderBy' => 'relevance',
                'printType' => 'books',
                'maxResults' => 10,
            ];

            if (filled($apiKey)) {
                $queryParams['key'] = $apiKey;
            }

            try {
                $response = Http::retry(2, 500, null, false)
                    ->timeout(20)
                    ->acceptJson()
                    ->get('https://www.googleapis.com/books/v1/volumes', $queryParams);
            } catch (\Throwable $e) {
                Log::warning('Google Books request failed.', [
                    'category' => $kategoriIsmi,
                    'error' => $e->getMessage(),
                ]);
                $this->command?->warn($kategoriIsmi . ' kategorisi icin API istegi basarisiz.');
                $this->seedFallbackBooks($kategori, $coverResolver);
                continue;
            }

            // Bazi anahtarlar Google Books API key degil (ornegin Gemini tokeni) olabiliyor.
            // Bu durumda key parametresini cikartip anonim istekle tekrar deniyoruz.
            if (
                $response->status() === 401 &&
                filled($apiKey) &&
                str_contains((string) $response->body(), 'API keys are not supported by this API')
            ) {
                $response = Http::retry(2, 500, null, false)
                    ->timeout(20)
                    ->acceptJson()
                    ->get('https://www.googleapis.com/books/v1/volumes', array_diff_key($queryParams, ['key' => true]));
            }

            if ($response->failed()) {
                Log::warning('Google Books API returned failure status.', [
                    'category' => $kategoriIsmi,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                $this->command?->warn($kategoriIsmi . ' kategorisi icin API hatasi: ' . $response->status());
                $this->seedFallbackBooks($kategori, $coverResolver);
                continue;
            }

            $books = data_get($response->json(), 'items', []);

            if (empty($books)) {
                $this->command?->warn($kategoriIsmi . ' kategorisi icin kitap bulunamadi.');
                $this->seedFallbackBooks($kategori, $coverResolver);
                continue;
            }

            foreach ($books as $item) {
                $info = data_get($item, 'volumeInfo', []);
                $title = trim((string) data_get($info, 'title', ''));

                if ($title === '') {
                    continue;
                }
                
                Book::updateOrCreate(
                    [
                        'title' => $title,
                        'author' => implode(', ', data_get($info, 'authors', ['Bilinmiyor'])),
                    ],
                    [
                        'image_url' => $coverResolver->pickBestLink(data_get($info, 'imageLinks', []))
                            ?? $coverResolver->resolve($title, implode(', ', data_get($info, 'authors', []))),
                        'description' => substr((string) data_get($info, 'description', 'Açıklama yok.'), 0, 999), // Veritabanı sınırına takılmamak için
                        'category_id' => $kategori->id,
                        'page_count' => (int) data_get($info, 'pageCount', 200),
                        'is_protected' => true,
                    ]
                );
            }
        }
    }

    private function seedFallbackBooks(Category $category, GoogleBooksCoverResolver $coverResolver): void
    {
        $items = self::FALLBACK_BOOKS[$category->name] ?? [];

        foreach ($items as $item) {
            $cover = $coverResolver->resolve($item['title'], $item['author']);

            Book::updateOrCreate(
                [
                    'title' => $item['title'],
                    'author' => $item['author'],
                ],
                [
                    'image_url' => $cover,
                    'description' => $item['title'] . ' kitabı için otomatik fallback kaydı.',
                    'category_id' => $category->id,
                    'page_count' => 300,
                    'is_protected' => true,
                ]
            );

            usleep(400000);
        }
    }
}