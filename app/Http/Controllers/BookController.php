<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Comment;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookController extends Controller
{
    // Keşfet sayfası (Kitap Listesi)
    public function index(Request $request)
    {
        $query = Book::query()
            ->with(['category'])
            ->withCount('comments')
            ->withRatingStats();

        if ($categoryId = $request->input('category')) {
            $query->where('category_id', $categoryId);
        }

        if ($search = trim((string) $request->input('q', ''))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%");
            });
        }

        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'title' => $query->orderBy('title'),
            'rating' => $query->orderByDesc('average_rating')->orderByDesc('ratings_count'),
            default => $query->latest(),
        };

        $books = $query->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('welcome', [
            'books' => $books,
            'categories' => $categories,
            'currentCategory' => $categoryId ?? null,
            'currentSort' => $sort,
            'searchQuery' => $search,
        ]);
    }

    // Kütüphane Durumu Güncelleme
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:okuyorum,okundu,okuyacagim'
        ]);

        $user = auth()->user();
        $existing = $user->books()->where('books.id', $id)->first();

        if ($existing && $existing->pivot->is_protected) {
            return back()->with('error', 'Bu kitap rafına eklendikten sonra başka rafa taşınamaz. 🔒');
        }

        $user->books()->syncWithoutDetaching([
            $id => [
                'status' => $request->status,
                'is_protected' => true,
                'rating' => $existing?->pivot->rating,
            ]
        ]);

        return back()->with('success', 'Kitap "' . $this->statusLabel($request->status) . '" rafına eklendi. Bu raf artık kilitli. 📚');
    }

    public function updateRating(Request $request, $id): JsonResponse|RedirectResponse
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $user = auth()->user();
        $existing = $user->books()->where('books.id', $id)->first();

        $pivotData = [
            'rating' => (int) $request->rating,
            'is_protected' => $existing ? (bool) $existing->pivot->is_protected : false,
            'status' => $existing?->pivot->status ?? 'okuyacagim',
        ];

        $user->books()->syncWithoutDetaching([
            $id => $pivotData,
        ]);

        if ($request->expectsJson()) {
            $book = Book::withRatingStats()->findOrFail($id);

            return response()->json([
                'user_rating' => (int) $request->rating,
                'average_rating' => $book->average_rating ? round((float) $book->average_rating, 1) : null,
                'ratings_count' => (int) $book->ratings_count,
            ]);
        }

        return back()->with('success', 'Puanın kaydedildi! ⭐');
    }

    // Kitap Detay Sayfası
    public function show($id)
    {
        $book = Book::with(['category', 'comments' => function ($query) {
            $query->with('user')->latest();
        }])
            ->withRatingStats()
            ->findOrFail($id);

        $userRating = null;
        if (auth()->check()) {
            $userRating = auth()->user()->books()
                ->where('books.id', $id)
                ->first()
                ?->pivot
                ?->rating;
        }

        return view('books', [
            'book' => $book,
            'userRating' => $userRating,
        ]);
    }

    // Yorum Kaydetme Metodu
    public function storeComment(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string|min:3|max:1000',
        ]);

        Comment::create([
            'user_id' => auth()->id(), 
            'book_id' => $id,          
            'content' => $request->content,
        ]);

        return redirect()->back()->with('success', 'Yorumunuz başarıyla eklendi!');
    }

    // Profil Sayfası Metodu
    public function profile()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user()->loadCount(['followers', 'following']);
        $userBooks = $user->books;
        $followers = $user->followers()->orderByPivot('created_at', 'desc')->get();
        $following = $user->following()->orderByPivot('created_at', 'desc')->get();

        $reading = $userBooks->where('pivot.status', 'okuyorum');
        $read = $userBooks->where('pivot.status', 'okundu');
        $willRead = $userBooks->where('pivot.status', 'okuyacagim');
        $posts = $user->posts()->with('book')->withLikeMeta($user)->paginate(10, ['*'], 'posts_page');

        return view('profile', [
            'user' => $user,
            'followers' => $followers,
            'following' => $following,
            'reading' => $reading,
            'read' => $read,
            'willRead' => $willRead,
            'posts' => $posts,
        ]);
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'okuyorum' => 'Okuyorum',
            'okundu' => 'Okundu',
            default => 'Okuyacağım',
        };
    }
}