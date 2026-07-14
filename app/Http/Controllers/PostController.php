<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function show(Request $request, Post $post): View
    {
        $viewer = $request->user();

        if (! $post->user->canBeViewedBy($viewer)) {
            abort(403, 'Bu paylaşımı görüntüleme yetkin yok.');
        }

        $post = Post::query()
            ->whereKey($post->id)
            ->with(['user', 'book'])
            ->withLikeMeta($viewer)
            ->firstOrFail();

        return view('posts.show', ['post' => $post]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'content' => ['required', 'string', 'min:3', 'max:2000'],
            'type' => ['required', 'in:thought,quote'],
            'book_id' => ['nullable', 'exists:books,id'],
        ]);

        if ($data['type'] === 'quote' && empty($data['book_id'])) {
            return back()->withErrors(['book_id' => 'Alıntı paylaşırken bir kitap seçmelisin.'])->withInput();
        }

        Post::create([
            'user_id' => $request->user()->id,
            'content' => $data['content'],
            'type' => $data['type'],
            'book_id' => $data['book_id'] ?? null,
        ]);

        return back()->with('success', 'Paylaşımın yayınlandı!');
    }

    public function toggleLike(Request $request, Post $post): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $like = $post->likes()->where('user_id', $user->id)->first();

        if ($like) {
            $like->delete();
            $liked = false;
            Notification::removePostLike($post, $user);
        } else {
            $post->likes()->create(['user_id' => $user->id]);
            $liked = true;
            Notification::recordPostLike($post, $user);
        }

        $likesCount = $post->likes()->count();

        if ($request->expectsJson()) {
            return response()->json([
                'liked' => $liked,
                'likes_count' => $likesCount,
            ]);
        }

        return back();
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        if ($post->user_id !== $request->user()->id) {
            abort(403);
        }

        $post->delete();

        return back()->with('success', 'Paylaşım silindi.');
    }
}
