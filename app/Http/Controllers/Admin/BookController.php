<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Services\GoogleBooksService;
use Illuminate\Http\JsonResponse;
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

    public function searchGoogleBooks(Request $request, GoogleBooksService $googleBooks): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:200'],
        ]);

        return response()->json([
            'results' => $googleBooks->search($validated['q']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->bookRules());

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
        $data = $request->validate($this->bookRules($book));

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

    /**
     * @return array<string, mixed>
     */
    private function bookRules(?Book $book = null): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) use ($book): void {
                    $normalized = mb_strtolower(trim((string) $value));

                    $exists = Book::query()
                        ->whereRaw('LOWER(TRIM(title)) = ?', [$normalized])
                        ->when($book, fn ($query) => $query->where('id', '!=', $book->id))
                        ->exists();

                    if ($exists) {
                        $fail('Bu kitap zaten eklenmiş.');
                    }
                },
            ],
            'author' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'image_url' => ['required', 'url'],
            'description' => ['required', 'string', 'min:10'],
            'page_count' => ['required', 'integer', 'min:1'],
            'is_protected' => ['sometimes', 'boolean'],
        ];
    }
}
