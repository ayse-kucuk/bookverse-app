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
            ->withLikeMeta($viewer)
            ->whereHas('user', fn ($query) => $query->visibleTo($viewer))
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
