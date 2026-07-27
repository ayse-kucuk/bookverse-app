<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use App\Services\AiBookRequestValidator;
use App\Services\AiRecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AiRecommendationController extends Controller
{
    public function recommend(Request $request, AiRecommendationService $ai, AiBookRequestValidator $validator): JsonResponse
    {
        $validated = $request->validate([
            'mood'     => ['nullable', 'string', 'max:80'],
            'genre_id' => ['nullable', 'integer', 'exists:categories,id'],
            'free_text'=> ['nullable', 'string', 'max:600'],
            'limit'    => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        /** @var User|null $user */
        $user = $request->user();

        $genreId     = $validated['genre_id'] ?? null;
        $limit       = (int) ($validated['limit'] ?? 5);
        $genreName   = null;
        $categoryIds = [];

        if ($genreId) {
            $categoryIds = [$genreId];
            $genreName   = Cache::remember("category_name_{$genreId}", 600, fn () => Category::find($genreId)?->name);
        }

        $validation = $validator->validate(
            $validated['mood'] ?? null,
            $genreName,
            $validated['free_text'] ?? null,
        );

        if (! $validation['valid']) {
            return response()->json([
                'recommendations' => [],
                'message' => $validation['message'],
                'hint' => __('ui.ai.invalid_request_hint'),
                'example' => __('ui.ai.invalid_request_example'),
                'source' => 'validation',
            ], 422);
        }

        // Build a deterministic cache key based on inputs
        $userId       = $user?->id ?? 0;
        $cacheKey     = 'ai_rec_' . md5(json_encode([$userId, $genreId, $validated['mood'] ?? '', $validated['free_text'] ?? '', $limit]));
        $cacheTtl     = 600; // 10 minutes

        // Don't cache when free_text is provided (too personalised)
        $useCache = empty($validated['free_text']);

        if ($useCache && Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        $candidateQuery = Book::query()
            ->with('category')
            ->whereNotNull('title')
            ->whereNotNull('category_id');

        if ($categoryIds !== []) {
            $candidateQuery->whereIn('category_id', $categoryIds);
        }

        if ($user) {
            $candidateQuery->whereNotIn('id', function ($q) use ($user) {
                $q->select('book_id')
                    ->from('book_user')
                    ->where('user_id', $user->id);
            });
        }

        $candidateBooks = $candidateQuery
            ->inRandomOrder()
            ->take(30)
            ->get(['id', 'title', 'author', 'category_id', 'image_url']);

        // Seçilen türde yeterli aday yoksa tüm kütüphaneye genişlet
        if ($candidateBooks->count() < 3 && $categoryIds !== []) {
            $fallbackQuery = Book::query()
                ->with('category')
                ->whereNotNull('title')
                ->whereNotNull('category_id');

            if ($user) {
                $fallbackQuery->whereNotIn('id', function ($q) use ($user) {
                    $q->select('book_id')
                        ->from('book_user')
                        ->where('user_id', $user->id);
                });
            }

            $candidateBooks = $fallbackQuery
                ->inRandomOrder()
                ->take(30)
                ->get(['id', 'title', 'author', 'category_id', 'image_url']);
        }

        if ($candidateBooks->isEmpty()) {
            return response()->json([
                'recommendations' => [],
                'message' => 'Şu an öneri için uygun kitap bulunamadı. Raftaki kitaplar hariç tutuluyor olabilir.',
            ]);
        }

        $candidateList = $candidateBooks->map(fn (Book $b) => [
            'id' => (int) $b->id,
            'title' => (string) $b->title,
            'author' => (string) $b->author,
            'category' => (string) ($b->category?->name ?? 'Genel'),
        ])->values()->all();

        $aiResult = $ai->recommend($user, [
            'mood' => $validated['mood'] ?? null,
            'genre_name' => $genreName,
            'free_text' => $validated['free_text'] ?? null,
            'limit' => $limit,
        ], $candidateList);

        $byId = $candidateBooks->keyBy('id');
        $byTitleAuthor = $candidateBooks->mapWithKeys(function (Book $b) {
            $key = $this->normalizeKey($b->title, $b->author);

            return [$key => $b];
        });
        $byTitle = $candidateBooks->mapWithKeys(function (Book $b) {
            return [$this->normalizeTitle($b->title) => $b];
        });

        $recommendations = [];
        $usedIds = [];

        foreach (($aiResult['recommendations'] ?? []) as $rec) {
            if (! is_array($rec)) {
                continue;
            }

            $book = null;
            $recId = (int) ($rec['id'] ?? $rec['book_id'] ?? 0);

            if ($recId > 0 && $byId->has($recId)) {
                $book = $byId->get($recId);
            }

            if (! $book) {
                $key = $this->normalizeKey((string) ($rec['title'] ?? ''), (string) ($rec['author'] ?? ''));
                $book = $byTitleAuthor->get($key);
            }

            if (! $book) {
                $book = $byTitle->get($this->normalizeTitle((string) ($rec['title'] ?? '')));
            }

            if (! $book || isset($usedIds[$book->id])) {
                continue;
            }

            $usedIds[$book->id] = true;

            $recommendations[] = [
                'title' => $book->title,
                'author' => $book->author,
                'genre' => $book->category?->name ?? 'Genel',
                'matchScore' => (int) ($rec['matchScore'] ?? $rec['match_score'] ?? 0),
                'reason' => (string) ($rec['reason'] ?? ''),
                'book_id' => $book->id,
                'image_url' => $book->image_url,
                'book_url' => $book->publicUrl(),
            ];
        }

        // AI kitap döndü ama eşleşme bozulduysa yedek listeyi kullan
        if ($recommendations === [] && ($aiResult['recommendations'] ?? []) !== []) {
            foreach (array_slice($candidateList, 0, $limit) as $index => $candidate) {
                $book = $byId->get($candidate['id'] ?? 0);
                if (! $book) {
                    continue;
                }

                $recommendations[] = [
                    'title' => $book->title,
                    'author' => $book->author,
                    'genre' => $book->category?->name ?? 'Genel',
                    'matchScore' => 90 - ($index * 4),
                    'reason' => 'AI önerisi eşleştirilemedi; kütüphaneden en uygun adaylar gösteriliyor.',
                    'book_id' => $book->id,
                    'image_url' => $book->image_url,
                    'book_url' => $book->publicUrl(),
                ];
            }
        }

        $payload = [
            'recommendations' => array_values($recommendations),
            'message'         => $aiResult['message'] ?? null,
            'source'          => $aiResult['source'] ?? null,
        ];

        if ($useCache) {
            Cache::put($cacheKey, $payload, $cacheTtl);
        }

        return response()->json($payload);
    }

    private function normalizeKey(string $title, string $author): string
    {
        return $this->normalizeTitle($title).'|'.$this->normalizeTitle($author);
    }

    private function normalizeTitle(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return $value;
    }
}