<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleBooksService
{
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

        $google = $this->searchGoogleBooks($query, $maxResults);

        if ($google['results'] !== []) {
            return [
                'results' => $google['results'],
                'source' => 'google_books',
                'message' => null,
            ];
        }

        $openLibraryResults = $this->searchOpenLibrary($query, $maxResults);

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
     * @return array{results: array<int, array<string, mixed>>, message: string|null}
     */
    private function searchGoogleBooks(string $query, int $maxResults): array
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
                fn (array $item) => $this->normalizeGoogleVolume($item),
                $items
            ))),
            'message' => $message,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function searchOpenLibrary(string $query, int $maxResults): array
    {
        try {
            $response = Http::retry(2, 500, null, false)
                ->timeout(15)
                ->acceptJson()
                ->get('https://openlibrary.org/search.json', [
                    'q' => $query,
                    'limit' => $maxResults,
                    'fields' => 'key,title,author_name,first_publish_year,cover_i,number_of_pages_median,first_sentence',
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
            fn (array $doc) => $this->normalizeOpenLibraryDoc($doc),
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
     * @return array<string, mixed>|null
     */
    private function normalizeGoogleVolume(array $item): ?array
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

        return [
            'google_id' => (string) data_get($item, 'id', ''),
            'title' => $title,
            'author' => $author,
            'description' => mb_substr($description, 0, 999),
            'page_count' => max(1, (int) data_get($info, 'pageCount', 200)),
            'image_url' => $imageUrl,
            'published_year' => $this->extractYear(data_get($info, 'publishedDate')),
        ];
    }

    /**
     * @param  array<string, mixed>  $doc
     * @return array<string, mixed>|null
     */
    private function normalizeOpenLibraryDoc(array $doc): ?array
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

        return [
            'google_id' => (string) data_get($doc, 'key', ''),
            'title' => $title,
            'author' => $author,
            'description' => mb_substr(trim($description), 0, 999),
            'page_count' => $pageCount > 0 ? $pageCount : 200,
            'image_url' => $imageUrl,
            'published_year' => data_get($doc, 'first_publish_year'),
        ];
    }

    private function extractYear(mixed $date): ?int
    {
        if (! is_string($date) || ! preg_match('/\d{4}/', $date, $matches)) {
            return null;
        }

        return (int) $matches[0];
    }
}
