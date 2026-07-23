<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleBooksService
{
    /**
     * Google / Open Library etiketleri → Bookverse kategori adları.
     *
     * @var array<string, string>
     */
    private const CATEGORY_ALIASES = [
        'fantasy' => 'Fantastik',
        'fantastik' => 'Fantastik',
        'epic fantasy' => 'Fantastik',
        'juvenile fiction' => 'Fantastik',
        'young adult fiction' => 'Fantastik',
        'fairy tales' => 'Fantastik',
        'fairy tale' => 'Fantastik',
        'masal' => 'Fantastik',
        'science fiction' => 'Bilim Kurgu',
        'sci-fi' => 'Bilim Kurgu',
        'scifi' => 'Bilim Kurgu',
        'bilim kurgu' => 'Bilim Kurgu',
        'dystopia' => 'Bilim Kurgu',
        'mystery' => 'Polisiye',
        'crime' => 'Polisiye',
        'detective' => 'Polisiye',
        'thriller' => 'Polisiye',
        'suspense' => 'Polisiye',
        'polisiye' => 'Polisiye',
        'classics' => 'Klasikler',
        'classic' => 'Klasikler',
        'klasikler' => 'Klasikler',
        'literary fiction' => 'Klasikler',
        'fiction' => 'Klasikler',
        'history' => 'Tarih',
        'historical' => 'Tarih',
        'tarih' => 'Tarih',
        'biography' => 'Biyografi',
        'biography & autobiography' => 'Biyografi',
        'autobiography' => 'Biyografi',
        'biyografi' => 'Biyografi',
        'psychology' => 'Psikoloji',
        'psikoloji' => 'Psikoloji',
        'self-help' => 'Kişisel Gelişim',
        'self help' => 'Kişisel Gelişim',
        'personal development' => 'Kişisel Gelişim',
        'kişisel gelişim' => 'Kişisel Gelişim',
    ];

    public function __construct(
        private GoogleBooksCoverResolver $coverResolver
    ) {}

    /**
     * @return array{results: array<int, array<string, mixed>>, source: string|null, message: string|null}
     */
    public function search(string $query, int $maxResults = 10): array
    {
        $query = trim($query);

        if ($query === '') {
            return [
                'results' => [],
                'source' => null,
                'message' => null,
            ];
        }

        $maxResults = max(1, min($maxResults, 20));
        $categories = Category::query()->orderBy('name')->get(['id', 'name']);

        $google = $this->searchGoogleBooks($query, $maxResults, $categories);

        if ($google['results'] !== []) {
            return [
                'results' => $google['results'],
                'source' => 'google_books',
                'message' => null,
            ];
        }

        $openLibraryResults = $this->searchOpenLibrary($query, $maxResults, $categories);

        if ($openLibraryResults !== []) {
            return [
                'results' => $openLibraryResults,
                'source' => 'open_library',
                'message' => $google['message'] ?? 'Open Library sonuçları gösteriliyor.',
            ];
        }

        return [
            'results' => [],
            'source' => null,
            'message' => $google['message'] ?? 'Sonuç bulunamadı.',
        ];
    }

    /**
     * @param  Collection<int, Category>  $categories
     * @return array{results: array<int, array<string, mixed>>, message: string|null}
     */
    private function searchGoogleBooks(string $query, int $maxResults, Collection $categories): array
    {
        $queryParams = [
            'q' => $query,
            'maxResults' => $maxResults,
            'printType' => 'books',
        ];

        $apiKey = config('services.google_books.key');
        $message = null;

        if (filled($apiKey)) {
            $queryParams['key'] = $apiKey;
        }

        $response = $this->request($queryParams);

        if ($this->isInvalidApiKeyResponse($response) && filled($apiKey)) {
            $message = 'GOOGLE_BOOKS_API_KEY geçersiz. Books API key kullanın veya .env\'den kaldırın.';
            unset($queryParams['key']);
            $response = $this->request($queryParams);
        }

        if (! $response) {
            return [
                'results' => [],
                'message' => $message ?? 'Google Books isteği başarısız oldu.',
            ];
        }

        if ($response->status() === 429) {
            return [
                'results' => [],
                'message' => $message ?? 'Google Books günlük kotası doldu. Open Library deneniyor…',
            ];
        }

        if ($response->failed()) {
            Log::warning('Google Books search failed.', [
                'query' => $query,
                'status' => $response->status(),
            ]);

            return [
                'results' => [],
                'message' => $message ?? 'Google Books şu an yanıt vermiyor.',
            ];
        }

        $items = data_get($response->json(), 'items', []);

        return [
            'results' => array_values(array_filter(array_map(
                fn (array $item) => $this->normalizeGoogleVolume($item, $categories),
                $items
            ))),
            'message' => $message,
        ];
    }

    /**
     * @param  Collection<int, Category>  $categories
     * @return array<int, array<string, mixed>>
     */
    private function searchOpenLibrary(string $query, int $maxResults, Collection $categories): array
    {
        try {
            $response = Http::retry(2, 500, null, false)
                ->timeout(15)
                ->acceptJson()
                ->get('https://openlibrary.org/search.json', [
                    'q' => $query,
                    'limit' => $maxResults,
                    'fields' => 'key,title,author_name,first_publish_year,cover_i,number_of_pages_median,first_sentence,subject',
                ]);
        } catch (\Throwable $e) {
            Log::warning('Open Library search request exception.', [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);

            return [];
        }

        if ($response->failed()) {
            return [];
        }

        $docs = data_get($response->json(), 'docs', []);

        return array_values(array_filter(array_map(
            fn (array $doc) => $this->normalizeOpenLibraryDoc($doc, $categories),
            $docs
        )));
    }

    /**
     * @param  array<string, mixed>  $queryParams
     */
    private function request(array $queryParams): ?Response
    {
        try {
            return Http::retry(2, 500, null, false)
                ->timeout(15)
                ->acceptJson()
                ->get('https://www.googleapis.com/books/v1/volumes', $queryParams);
        } catch (\Throwable $e) {
            Log::warning('Google Books search request exception.', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function isInvalidApiKeyResponse(?Response $response): bool
    {
        return $response?->status() === 401
            && str_contains((string) $response->body(), 'API keys are not supported by this API');
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  Collection<int, Category>  $categories
     * @return array<string, mixed>|null
     */
    private function normalizeGoogleVolume(array $item, Collection $categories): ?array
    {
        $info = data_get($item, 'volumeInfo', []);
        $title = trim((string) data_get($info, 'title', ''));

        if ($title === '') {
            return null;
        }

        $authors = data_get($info, 'authors', []);
        $author = is_array($authors) && $authors !== []
            ? implode(', ', $authors)
            : 'Bilinmiyor';

        $description = trim((string) data_get($info, 'description', ''));

        if (mb_strlen($description) < 10) {
            $description = $title.' — Google Books kaydı.';
        }

        $imageUrl = $this->coverResolver->pickBestLink(data_get($info, 'imageLinks', []));
        $rawCategories = data_get($info, 'categories', []);
        $rawCategories = is_array($rawCategories) ? $rawCategories : [];
        $matched = $this->matchCategory($rawCategories, $categories);

        return [
            'google_id' => (string) data_get($item, 'id', ''),
            'title' => $title,
            'author' => $author,
            'description' => mb_substr($description, 0, 999),
            'page_count' => max(1, (int) data_get($info, 'pageCount', 200)),
            'image_url' => $imageUrl,
            'published_year' => $this->extractYear(data_get($info, 'publishedDate')),
            'category_id' => $matched['id'],
            'category' => $matched['name'],
            'category_labels' => $matched['labels'],
        ];
    }

    /**
     * @param  array<string, mixed>  $doc
     * @param  Collection<int, Category>  $categories
     * @return array<string, mixed>|null
     */
    private function normalizeOpenLibraryDoc(array $doc, Collection $categories): ?array
    {
        $title = trim((string) data_get($doc, 'title', ''));

        if ($title === '') {
            return null;
        }

        $authors = data_get($doc, 'author_name', []);
        $author = is_array($authors) && $authors !== []
            ? implode(', ', $authors)
            : 'Bilinmiyor';

        $firstSentence = data_get($doc, 'first_sentence');
        $description = is_array($firstSentence)
            ? implode(' ', $firstSentence)
            : (string) $firstSentence;

        if (mb_strlen(trim($description)) < 10) {
            $description = $title.' — Open Library kaydı.';
        }

        $coverId = data_get($doc, 'cover_i');
        $imageUrl = $coverId
            ? 'https://covers.openlibrary.org/b/id/'.$coverId.'-L.jpg'
            : null;

        $pageCount = (int) data_get($doc, 'number_of_pages_median', 0);
        $subjects = data_get($doc, 'subject', []);
        $subjects = is_array($subjects) ? array_slice($subjects, 0, 8) : [];
        $matched = $this->matchCategory($subjects, $categories);

        return [
            'google_id' => (string) data_get($doc, 'key', ''),
            'title' => $title,
            'author' => $author,
            'description' => mb_substr(trim($description), 0, 999),
            'page_count' => $pageCount > 0 ? $pageCount : 200,
            'image_url' => $imageUrl,
            'published_year' => data_get($doc, 'first_publish_year'),
            'category_id' => $matched['id'],
            'category' => $matched['name'],
            'category_labels' => $matched['labels'],
        ];
    }

    /**
     * @param  array<int, mixed>  $rawLabels
     * @param  Collection<int, Category>  $categories
     * @return array{id: int|null, name: string|null, labels: array<int, string>}
     */
    private function matchCategory(array $rawLabels, Collection $categories): array
    {
        $labels = [];
        foreach ($rawLabels as $label) {
            if (! is_string($label)) {
                continue;
            }

            $clean = trim($label);
            if ($clean !== '') {
                $labels[] = $clean;
            }
        }

        if ($labels === [] || $categories->isEmpty()) {
            return ['id' => null, 'name' => null, 'labels' => $labels];
        }

        $byName = $categories->keyBy(fn (Category $c) => Str::lower(trim($c->name)));

        foreach ($labels as $label) {
            $parts = preg_split('/\s*\/\s*|\s*>\s*/', $label) ?: [$label];

            foreach ($parts as $part) {
                $normalized = Str::lower(trim($part));
                if ($normalized === '') {
                    continue;
                }

                if ($byName->has($normalized)) {
                    $category = $byName->get($normalized);

                    return [
                        'id' => (int) $category->id,
                        'name' => $category->name,
                        'labels' => $labels,
                    ];
                }

                $canonical = self::CATEGORY_ALIASES[$normalized] ?? null;
                if ($canonical && $byName->has(Str::lower($canonical))) {
                    $category = $byName->get(Str::lower($canonical));

                    return [
                        'id' => (int) $category->id,
                        'name' => $category->name,
                        'labels' => $labels,
                    ];
                }

                // Kısmi eşleşme: "Science Fiction - General" → "science fiction"
                foreach (self::CATEGORY_ALIASES as $alias => $canonicalName) {
                    if (str_contains($normalized, $alias) && $byName->has(Str::lower($canonicalName))) {
                        $category = $byName->get(Str::lower($canonicalName));

                        return [
                            'id' => (int) $category->id,
                            'name' => $category->name,
                            'labels' => $labels,
                        ];
                    }
                }

                foreach ($byName as $nameKey => $category) {
                    if (str_contains($normalized, $nameKey) || str_contains($nameKey, $normalized)) {
                        return [
                            'id' => (int) $category->id,
                            'name' => $category->name,
                            'labels' => $labels,
                        ];
                    }
                }
            }
        }

        return ['id' => null, 'name' => null, 'labels' => $labels];
    }

    private function extractYear(mixed $date): ?int
    {
        if (! is_string($date) || ! preg_match('/\d{4}/', $date, $matches)) {
            return null;
        }

        return (int) $matches[0];
    }
}
