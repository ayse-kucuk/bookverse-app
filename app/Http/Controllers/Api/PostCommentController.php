<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Post;
use App\Models\PostComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PostCommentController extends Controller
{
    #[OA\Post(
        path: '/posts/{post}/comments',
        summary: 'Paylaşıma yorum ekle',
        security: [['sanctum' => []]],
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(name: 'post', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['content'],
                properties: [
                    new OA\Property(property: 'content', type: 'string', example: 'Çok güzel bir paylaşım!'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Yorum eklendi'),
            new OA\Response(response: 403, description: 'Yetkisiz'),
            new OA\Response(response: 422, description: 'Doğrulama hatası'),
        ]
    )]
    public function store(Request $request, Post $post): JsonResponse
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

        return response()->json([
            'data' => $comment,
            'comments_count' => $post->comments()->count(),
        ], 201);
    }

    #[OA\Delete(
        path: '/posts/{post}/comments/{comment}',
        summary: 'Yorumu sil',
        security: [['sanctum' => []]],
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(name: 'post', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'comment', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Silindi'),
            new OA\Response(response: 403, description: 'Yetkisiz'),
        ]
    )]
    public function destroy(Request $request, Post $post, PostComment $comment): JsonResponse
    {
        if ($comment->post_id !== $post->id) {
            abort(404);
        }

        if ($comment->user_id !== $request->user()->id && $post->user_id !== $request->user()->id) {
            abort(403);
        }

        $comment->delete();

        return response()->json([
            'message' => 'Yorum silindi.',
            'comments_count' => $post->comments()->count(),
        ]);
    }
}
