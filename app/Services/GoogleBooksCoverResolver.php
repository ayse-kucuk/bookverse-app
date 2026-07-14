<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleBooksCoverResolver
{
    /** @var array<string, string> */
    private const TITLE_SEARCH_ALIASES = [
        'Cam Şato (Throne of Glass)' => 'Throne of Glass Sarah J. Maas',
        'Yuzuklerin Efendisi: Yuzuk Kardesligi' => 'The Fellowship of the Ring Tolkien',
        'Harry Potter ve Felsefe Tasi' => 'Harry Potter and the Philosopher Stone',
        'Hobbit' => 'The Hobbit',
        'Vakıf' => 'Foundation Isaac Asimov',
        'Suc ve Ceza' => 'Crime and Punishment',
        'Sefiller' => 'Les Miserables',
        'Sherlock Holmes: Kizil Dosya' => 'A Study in Scarlet',
        'Dogu Ekspresinde Cinayet' => 'Murder on the Orient Express',
        'Milenyum 1: Ejderha Dovmeli Kiz' => 'The Girl with the Dragon Tattoo',
    ];

    public function resolve(string $title, ?string $author = null): ?string
    {
        $queries = array_filter([
            $title,
            self::TITLE_SEARCH_ALIASES[$title] ?? null,
        ]);

        foreach ($queries as $query) {
            $cover = $this->resolveFromGoogleBooks($query, $author)
                ?? $this->resolveFromOpenLibrary($query, $author);

            if ($cover) {
                return $cover;
            }

            usleep(300000);
        }

        return null;
    }

    private function resolveFromGoogleBooks(string $title, ?string $author): ?string
    {
        $query = 'intitle:' . $title;
        if ($author) {
            $query .= '+inauthor:' . trim(explode(',', $author)[0]);
        }

        try {
            $response = Http::retry(2, 800, null, false)
                ->timeout(20)
                ->acceptJson()
                ->get('https://www.googleapis.com/books/v1/volumes', [
                    'q' => $query,
                    'maxResults' => 1,
                    'printType' => 'books',
                ]);
        } catch (\Throwable $e) {
            Log::warning('Google Books cover lookup failed.', [
                'title' => $title,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if ($response->failed()) {
            return null;
        }

        return $this->pickBestLink(data_get($response->json(), 'items.0.volumeInfo.imageLinks', []));
    }

    private function resolveFromOpenLibrary(string $title, ?string $author): ?string
    {
        try {
            $response = Http::retry(2, 800, null, false)
                ->timeout(20)
                ->acceptJson()
                ->get('https://openlibrary.org/search.json', array_filter([
                    'title' => $title,
                    'author' => $author ? trim(explode(',', $author)[0]) : null,
                    'limit' => 1,
                    'fields' => 'cover_i,isbn',
                ]));
        } catch (\Throwable $e) {
            return null;
        }

        if ($response->failed()) {
            return null;
        }

        $doc = data_get($response->json(), 'docs.0', []);

        if ($coverId = data_get($doc, 'cover_i')) {
            return 'https://covers.openlibrary.org/b/id/' . $coverId . '-L.jpg';
        }

        if ($isbn = data_get($doc, 'isbn.0')) {
            return 'https://covers.openlibrary.org/b/isbn/' . $isbn . '-L.jpg';
        }

        return null;
    }

    /**
     * @param  array<string, string>  $links
     */
    public function pickBestLink(array $links): ?string
    {
        $url = $links['extraLarge']
            ?? $links['large']
            ?? $links['medium']
            ?? $links['thumbnail']
            ?? $links['smallThumbnail']
            ?? null;

        if (! $url) {
            return null;
        }

        $url = str_replace('http://', 'https://', $url);

        return (string) preg_replace('/&zoom=\d+/', '&zoom=0', $url);
    }
}
