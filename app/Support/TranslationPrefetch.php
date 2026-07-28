<?php

namespace App\Support;

use App\Models\Book;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostComment;
use App\Services\ContentTranslationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TranslationPrefetch
{
    public static function warmForPosts(iterable $posts): void
    {
        $texts = [];

        foreach ($posts as $post) {
            if ($post instanceof Post) {
                $texts[] = $post->content;

                if ($post->relationLoaded('comments')) {
                    foreach ($post->comments as $comment) {
                        if ($comment instanceof PostComment) {
                            $texts[] = $comment->content;
                        }
                    }
                }
            }
        }

        self::service()->warm($texts, context: 'post');
    }

    public static function warmForBooks(iterable $books): void
    {
        $texts = [];

        foreach ($books as $book) {
            if (! $book instanceof Book) {
                continue;
            }

            $texts[] = $book->description;

            if ($book->relationLoaded('category') && $book->category) {
                $texts[] = $book->category->name;
            }

            if ($book->relationLoaded('comments')) {
                foreach ($book->comments as $comment) {
                    if ($comment instanceof Comment) {
                        $texts[] = $comment->content;
                    }
                }
            }
        }

        self::service()->warm($texts, context: 'book');
    }

    public static function warmForCategories(iterable $categories): void
    {
        $texts = [];

        foreach ($categories as $category) {
            if ($category instanceof Category) {
                $texts[] = $category->name;
                $texts[] = $category->description;
            }
        }

        self::service()->warm($texts, context: 'category');
    }

    public static function warmSearchResults(array $results): void
    {
        self::warmForBooks($results['books'] ?? []);
        self::warmForPosts($results['posts'] ?? []);

        if (($results['books'] ?? null) instanceof Collection) {
            foreach ($results['books'] as $book) {
                if ($book->relationLoaded('category') && $book->category) {
                    self::service()->warm([$book->category->name], context: 'category');
                }
            }
        }
    }

    public static function warmPaginatorPosts(LengthAwarePaginator $paginator): void
    {
        self::warmForPosts($paginator->items());
    }

    public static function warmPaginatorBooks(LengthAwarePaginator $paginator): void
    {
        self::warmForBooks($paginator->items());
    }

    private static function service(): ContentTranslationService
    {
        return app(ContentTranslationService::class);
    }
}
