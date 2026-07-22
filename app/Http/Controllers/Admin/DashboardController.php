<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'users' => User::count(),
                'books' => Book::count(),
                'categories' => Category::count(),
                'posts' => Post::count(),
                'comments' => Comment::count(),
                'admins' => User::where('is_admin', true)->count(),
            ],
            'recentBooks' => Book::with('category')->latest()->take(5)->get(),
            'recentUsers' => User::latest()->take(5)->get(),
            'recentComments' => Comment::with(['user', 'book'])->latest()->take(5)->get(),
        ]);
    }
}
