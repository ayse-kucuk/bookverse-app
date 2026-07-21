<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use OpenApi\Attributes as OA;

class SearchController extends Controller
{
    #[OA\Get(
        path: '/search',
        summary: 'Kitap, kullanıcı ve paylaşım ara',
        tags: ['Search'],
        parameters: [
            new OA\Parameter(name: 'q', in: 'query', required: true, schema: new OA\Schema(type: 'string'), example: 'cinayet'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Arama sonuçları'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('q', ''));
        $viewer = $request->user('sanctum');

        if (mb_strlen($query) < 1) {
            return response()->json([
                'books' => [],
                'users' => [],
                'posts' => [],
            ]);
        }

        return response()->json($this->performSearch($viewer, $query));
    }

    /**
     * @return array{books: Collection, users: Collection, posts: Collection}
     */
    private function performSearch(?User $viewer, string $query): array
    {
        $books = Book::query()
            ->with('category')
            ->withRatingStats()
            ->get()
            ->filter(fn (Book $book) => $this->matches($query, $book->title) || $this->matches($query, $book->author))
            ->take(20)
            ->values();

        $users = User::query()
            ->visibleTo($viewer)
            ->get()
            ->filter(fn (User $user) => $this->matches($query, $user->name))
            ->take(20)
            ->values();

        $posts = Post::query()
            ->with(['user', 'book'])
            ->whereHas('user', fn ($builder) => $builder->visibleTo($viewer))
            ->latest()
            ->limit(100)
            ->get()
            ->filter(fn (Post $post) => $this->matches($query, $post->content))
            ->take(20)
            ->values();

        return compact('books', 'users', 'posts');
    }

    private function matches(string $query, string $text): bool
    {
        $query = mb_strtolower(trim($query));
        $text = mb_strtolower(trim($text));

        if ($query === '' || $text === '') {
            return false;
        }

        if (str_contains($text, $query)) {
            return true;
        }

        foreach (preg_split('/\s+/u', $text) ?: [] as $word) {
            if ($word !== '' && str_starts_with($word, $query)) {
                return true;
            }
        }

        return false;
    }
}
