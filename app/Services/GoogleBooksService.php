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
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query, int $maxResults = 10): array
    {
        $query = trim($query);

        if ($query === '') {
            return [];
        }

        $maxResults = max(1, min($maxResults, 20));

        $queryParams = [
            'q' => $query,
            'maxResults' => $maxResults,
            'printType' => 'books',
            'langRestrict' => 'tr',
        ];

        $apiKey = config('services.google_books.key');

        if (filled($apiKey)) {
            $queryParams['key'] = $apiKey;
        }

        $response = $this->request($queryParams);

        if (
            $response?->status() === 401
            && filled($apiKey)
            && str_contains((string) $response->body(), 'API keys are not supported by this API')
        ) {
            unset($queryParams['key']);
            $response = $this->request($queryParams);
        }

        if (! $response || $response->failed()) {
            Log::warning('Google Books search failed.', [
                'query' => $query,
                'status' => $response?->status(),
            ]);

            return [];
        }

        $items = data_get($response->json(), 'items', []);

        return array_values(array_filter(array_map(
            fn (array $item) => $this->normalizeVolume($item),
            $items
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

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>|null
     */
    private function normalizeVolume(array $item): ?array
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

    private function extractYear(mixed $date): ?int
    {
        if (! is_string($date) || ! preg_match('/\d{4}/', $date, $matches)) {
            return null;
        }

        return (int) $matches[0];
    }
}
