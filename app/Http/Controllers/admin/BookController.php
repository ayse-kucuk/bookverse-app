<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $query = Book::query()->with('category')->withCount('comments');

        if ($search = trim((string) $request->input('q', ''))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category')) {
            $query->where('category_id', $categoryId);
        }

        return view('admin.books.index', [
            'books' => $query->latest()->paginate(15)->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
            'search' => $search,
            'currentCategory' => $categoryId,
        ]);
    }

    public function create(): View
    {
        return view('admin.books.create', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'image_url' => ['required', 'url'],
            'description' => ['required', 'string', 'min:10'],
            'page_count' => ['required', 'integer', 'min:1'],
            'is_protected' => ['sometimes', 'boolean'],
        ]);

        $data['is_protected'] = $request->boolean('is_protected', true);

        Book::create($data);

        return redirect()->route('admin.books.index')->with('success', 'Kitap eklendi.');
    }

    public function edit(Book $book): View
    {
        return view('admin.books.edit', [
            'book' => $book,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'image_url' => ['required', 'url'],
            'description' => ['required', 'string', 'min:10'],
            'page_count' => ['required', 'integer', 'min:1'],
            'is_protected' => ['sometimes', 'boolean'],
        ]);

        $data['is_protected'] = $request->boolean('is_protected');

        $book->update($data);

        return redirect()->route('admin.books.index')->with('success', 'Kitap güncellendi.');
    }

    public function destroy(Book $book): RedirectResponse
    {
        Book::withoutEvents(function () use ($book) {
            $book->comments()->delete();
            $book->users()->detach();
            $book->delete();
        });

        return redirect()->route('admin.books.index')->with('success', 'Kitap silindi.');
    }
}
