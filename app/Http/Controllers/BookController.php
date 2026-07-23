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
            $query->matchingSearchTerm($search);
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
        $rating = (int) $request->rating;

        $pivotData = [
            'rating' => $rating,
            'is_protected' => $existing ? (bool) $existing->pivot->is_protected : false,
            'status' => $existing?->pivot->status ?? 'okuyacagim',
        ];

        $user->books()->syncWithoutDetaching([
            $id => $pivotData,
        ]);

        // Varsa incelemedeki yıldızı da senkron tut
        Comment::query()
            ->where('book_id', $id)
            ->where('user_id', $user->id)
            ->whereNotNull('rating')
            ->update(['rating' => $rating]);

        if ($request->expectsJson()) {
            $book = Book::withRatingStats()->findOrFail($id);

            return response()->json([
                'user_rating' => $rating,
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
        $userReview = null;

        if (auth()->check()) {
            $userRating = auth()->user()->books()
                ->where('books.id', $id)
                ->first()
                ?->pivot
                ?->rating;

            $userReview = $book->comments->firstWhere('user_id', auth()->id());
        }

        return view('books', [
            'book' => $book,
            'userRating' => $userRating,
            'userReview' => $userReview,
        ]);
    }

    // Kitap incelemesi (yıldız + metin) — kullanıcı başına bir kayıt
    public function storeComment(Request $request, $id)
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'min:10', 'max:2000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $user = auth()->user();
        $rating = (int) $validated['rating'];

        $comment = Comment::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'book_id' => $id,
            ],
            [
                'content' => $validated['content'],
                'rating' => $rating,
            ]
        );

        $existing = $user->books()->where('books.id', $id)->first();

        $user->books()->syncWithoutDetaching([
            $id => [
                'rating' => $rating,
                'status' => $existing?->pivot->status ?? 'okuyacagim',
                'is_protected' => $existing ? (bool) $existing->pivot->is_protected : false,
            ],
        ]);

        $message = $comment->wasRecentlyCreated
            ? 'İncelemen yayınlandı!'
            : 'İncelemen güncellendi!';

        return redirect()->back()->with('success', $message);
    }

    public function destroyComment(Request $request, $id, Comment $comment): RedirectResponse
    {
        abort_unless($comment->book_id == $id, 404);
        abort_unless(
            (int) $comment->user_id === (int) auth()->id() || (bool) auth()->user()?->is_admin,
            403
        );

        $comment->delete();

        return redirect()->back()->with('success', 'İnceleme silindi.');
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