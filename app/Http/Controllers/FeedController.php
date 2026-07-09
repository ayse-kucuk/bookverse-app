<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedController extends Controller
{
    public function index(Request $request): View
    {
        $viewer = $request->user();

        $posts = Post::with(['user', 'book'])
            ->whereHas('user', function ($query) use ($viewer) {
                if (! $viewer) {
                    $query->where('account_visibility', User::VISIBILITY_PUBLIC);

                    return;
                }

                $followingIds = $viewer->following()->pluck('users.id');

                $query->where(function ($inner) use ($viewer, $followingIds) {
                    $inner->where('users.id', $viewer->id)
                        ->orWhere('account_visibility', User::VISIBILITY_PUBLIC)
                        ->orWhere(function ($private) use ($followingIds) {
                            $private->where('account_visibility', User::VISIBILITY_FOLLOWERS)
                                ->whereIn('users.id', $followingIds);
                        });
                });
            })
            ->latest()
            ->paginate(15);

        $books = Book::orderBy('title')->get(['id', 'title', 'author']);
        $exploreBooks = Book::with('category')->latest()->take(4)->get();

        return view('feed', [
            'posts' => $posts,
            'books' => $books,
            'exploreBooks' => $exploreBooks,
        ]);
    }
}
