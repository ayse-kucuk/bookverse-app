<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use App\Services\AiRecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiRecommendationController extends Controller
{
    public function recommend(Request $request, AiRecommendationService $ai): JsonResponse
    {
        $validated = $request->validate([
            'mood' => ['nullable', 'string', 'max:80'],
            'genre_id' => ['nullable', 'integer', 'exists:categories,id'],
            'free_text' => ['nullable', 'string', 'max:600'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        /** @var User|null $user */
        $user = $request->user();

        $genreId = $validated['genre_id'] ?? null;
        $limit = (int) ($validated['limit'] ?? 5);

        $categoryIds = [];
        $genreName = null;

        if ($genreId) {
            $categoryIds = [$genreId];
            $genreName = Category::find($genreId)?->name;
        } elseif ($user) {
            // Tarihçeden favori türleri çıkar.
            $categoryIds = Category::query()
                ->select('categories.id')
                ->join('books', 'categories.id', '=', 'books.category_id')
                ->join('book_user', 'books.id', '=', 'book_user.book_id')
                ->where('book_user.user_id', $user->id)
                ->whereIn('book_user.status', ['okundu', 'okuyorum'])
                ->groupBy('categories.id')
                ->orderByRaw('COUNT(*) DESC')
                ->limit(3)
                ->pluck('categories.id')
                ->all();

            $genreName = $categoryIds
                ? (string) Category::find($categoryIds[0])?->name
                : null;
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
            ->latest()
            ->take(25)
            ->get(['id', 'title', 'author', 'category_id', 'image_url']);

        if ($candidateBooks->isEmpty()) {
            return response()->json([
                'recommendations' => [],
                'message' => 'Şu an öneri için uygun kitap bulunamadı.',
            ]);
        }

        $candidateList = $candidateBooks->map(fn (Book $b) => [
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

        $bookIndex = $candidateBooks->mapWithKeys(function (Book $b) {
            $key = mb_strtolower(trim((string) $b->title)).'|'.mb_strtolower(trim((string) $b->author));
            return [$key => $b];
        });

        $recommendations = [];

        foreach (($aiResult['recommendations'] ?? []) as $rec) {
            $title = (string) ($rec['title'] ?? '');
            $author = (string) ($rec['author'] ?? '');
            $key = mb_strtolower(trim($title)).'|'.mb_strtolower(trim($author));

            $book = $bookIndex->get($key);
            if (! $book) {
                continue;
            }

            $recommendations[] = [
                'title' => $book->title,
                'author' => $book->author,
                'genre' => $book->category?->name ?? 'Genel',
                'matchScore' => (int) ($rec['matchScore'] ?? 0),
                'reason' => (string) ($rec['reason'] ?? ''),
                'book_id' => $book->id,
                'image_url' => $book->image_url,
                'book_url' => route('books.show', $book->id),
            ];
        }

        return response()->json([
            'recommendations' => array_values($recommendations),
            'message' => $aiResult['message'] ?? null,
        ]);
    }
}

