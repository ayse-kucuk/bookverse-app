<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Post;
use App\Models\PostComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostCommentController extends Controller
{
    public function store(Request $request, Post $post): JsonResponse|RedirectResponse
    {
        $viewer = $request->user();

        if (! $post->user->canBeViewedBy($viewer)) {
            abort(403, 'Bu paylaşıma yorum yapamazsın.');
        }

        $data = $request->validate([
            'content' => ['required', 'string', 'min:1', 'max:1000'],
        ]);

        $comment = $post->comments()->create([
            'user_id' => $viewer->id,
            'content' => $data['content'],
        ]);

        Notification::recordPostComment($post, $viewer);

        $comment->load('user');

        if ($request->expectsJson()) {
            return response()->json([
                'comment' => $comment,
                'comments_count' => $post->comments()->count(),
            ], 201);
        }

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Yorumun eklendi.')
            ->withFragment('comments');
    }

    public function destroy(Request $request, Post $post, PostComment $comment): JsonResponse|RedirectResponse
    {
        if ($comment->post_id !== $post->id) {
            abort(404);
        }

        if ($comment->user_id !== $request->user()->id && $post->user_id !== $request->user()->id) {
            abort(403);
        }

        $comment->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Yorum silindi.',
                'comments_count' => $post->comments()->count(),
            ]);
        }

        return back()->with('success', 'Yorum silindi.');
    }
}
