<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->input('q', ''));
        $results = $this->performSearch($request->user(), $query);

        return view('search', [
            'query' => $query,
            'books' => $results['books'],
            'users' => $results['users'],
            'posts' => $results['posts'],
        ]);
    }

    public function suggest(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('q', ''));

        if (mb_strlen($query) < 1) {
            return response()->json([
                'books' => [],
                'users' => [],
                'posts' => [],
            ]);
        }

        $results = $this->performSearch($request->user(), $query, booksLimit: 6, usersLimit: 4, postsLimit: 3);

        return response()->json([
            'books' => $results['books']->map(fn (Book $book) => [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author,
                'image_url' => $book->image_url,
                'url' => route('books.show', $book),
            ])->values(),
            'users' => $results['users']->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'url' => route('users.show', $user),
            ])->values(),
            'posts' => $results['posts']->map(fn (Post $post) => [
                'id' => $post->id,
                'excerpt' => mb_strimwidth($post->content, 0, 80, '…'),
                'author' => $post->user->name,
                'url' => route('posts.show', $post),
            ])->values(),
            'search_url' => route('search', ['q' => $query]),
        ]);
    }

    /**
     * @return array{books: Collection, users: Collection, posts: Collection}
     */
    private function performSearch(?User $viewer, string $query, int $booksLimit = 20, int $usersLimit = 20, int $postsLimit = 20): array
    {
        $books = collect();
        $users = collect();
        $posts = collect();

        if (mb_strlen($query) < 1) {
            return compact('books', 'users', 'posts');
        }

        $books = Book::query()
            ->with('category')
            ->withRatingStats()
            ->get()
            ->filter(fn (Book $book) => $this->matchesSearch($query, $book->title)
                || $this->matchesSearch($query, $book->author))
            ->sortBy(fn (Book $book) => $this->matchRank($query, $book->title, $book->author))
            ->take($booksLimit)
            ->values();

        $users = User::query()
            ->visibleTo($viewer)
            ->orderBy('name')
            ->get()
            ->filter(fn (User $user) => $this->matchesSearch($query, $user->name))
            ->sortBy(fn (User $user) => $this->matchRank($query, $user->name))
            ->take($usersLimit)
            ->values();

        $posts = Post::query()
            ->with(['user', 'book'])
            ->withLikeMeta($viewer)
            ->whereHas('user', fn ($builder) => $builder->visibleTo($viewer))
            ->latest()
            ->limit(300)
            ->get()
            ->filter(fn (Post $post) => $this->matchesSearch($query, $post->content))
            ->sortBy(fn (Post $post) => $this->matchRank($query, $post->content))
            ->take($postsLimit)
            ->values();

        return compact('books', 'users', 'posts');
    }

    private function matchesSearch(string $query, string $text): bool
    {
        $query = mb_strtolower(trim($query));
        $text = mb_strtolower(trim($text));

        if ($query === '' || $text === '') {
            return false;
        }

        if (str_starts_with($text, $query)) {
            return true;
        }

        $words = preg_split('/\s+/u', $text) ?: [];

        foreach ($words as $word) {
            if ($word === '') {
                continue;
            }

            if (str_starts_with($word, $query)) {
                return true;
            }

            if ($this->isFuzzyMatch($query, $word)) {
                return true;
            }
        }

        return false;
    }

    private function isFuzzyMatch(string $query, string $word): bool
    {
        if (mb_strlen($query) < 3) {
            return false;
        }

        $lengthDiff = abs(mb_strlen($word) - mb_strlen($query));
        if ($lengthDiff > 2) {
            return false;
        }

        similar_text($query, $word, $percent);

        if ($percent >= 72) {
            return true;
        }

        if (preg_match('/^[\x20-\x7E]+$/', $query.$word)) {
            $maxDistance = mb_strlen($query) <= 5 ? 1 : 2;

            return levenshtein($query, $word) <= $maxDistance;
        }

        return false;
    }

    private function matchRank(string $query, string ...$fields): int
    {
        $query = mb_strtolower(trim($query));
        $best = 99;

        foreach ($fields as $field) {
            $text = mb_strtolower(trim($field));

            if (str_starts_with($text, $query)) {
                return min($best, 0);
            }

            foreach (preg_split('/\s+/u', $text) ?: [] as $word) {
                if ($word === '') {
                    continue;
                }

                if (str_starts_with($word, $query)) {
                    $best = min($best, 1);
                } elseif ($this->isFuzzyMatch($query, $word)) {
                    $best = min($best, 2);
                }
            }
        }

        return $best;
    }
}
