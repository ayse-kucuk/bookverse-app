<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PostController extends Controller
{
    #[OA\Get(
        path: '/posts',
        summary: 'Akış (görünür paylaşımlar)',
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paylaşım listesi'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $viewer = $request->user('sanctum');

        $posts = Post::with(['user', 'book'])
            ->withLikeMeta($viewer)
            ->whereHas('user', fn ($query) => $query->visibleTo($viewer))
            ->latest()
            ->paginate(15);

        return response()->json($posts);
    }

    #[OA\Get(
        path: '/posts/{post}',
        summary: 'Paylaşım detayı',
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(name: 'post', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paylaşım'),
            new OA\Response(response: 403, description: 'Görüntüleme yetkisi yok'),
            new OA\Response(response: 404, description: 'Bulunamadı'),
        ]
    )]
    public function show(Request $request, Post $post): JsonResponse
    {
        $viewer = $request->user('sanctum');

        if (! $post->user->canBeViewedBy($viewer)) {
            abort(403, 'Bu paylaşımı görüntüleme yetkin yok.');
        }

        $post = Post::query()
            ->whereKey($post->id)
            ->with(['user', 'book', 'comments.user'])
            ->withLikeMeta($viewer)
            ->firstOrFail();

        return response()->json(['data' => $post]);
    }

    #[OA\Post(
        path: '/posts',
        summary: 'Yeni paylaşım oluştur',
        security: [['sanctum' => []]],
        tags: ['Posts'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['content', 'type'],
                properties: [
                    new OA\Property(property: 'content', type: 'string', example: 'Bu kitabı çok beğendim.'),
                    new OA\Property(property: 'type', type: 'string', enum: ['thought', 'quote'], example: 'thought'),
                    new OA\Property(property: 'book_id', type: 'integer', nullable: true, example: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Oluşturuldu'),
            new OA\Response(response: 401, description: 'Yetkisiz'),
            new OA\Response(response: 422, description: 'Doğrulama hatası'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'content' => ['required', 'string', 'min:3', 'max:2000'],
            'type' => ['required', 'in:thought,quote'],
            'book_id' => ['nullable', 'exists:books,id'],
        ]);

        if ($data['type'] === 'quote' && empty($data['book_id'])) {
            return response()->json([
                'message' => 'Alıntı paylaşırken bir kitap seçmelisin.',
                'errors' => ['book_id' => ['Alıntı paylaşırken bir kitap seçmelisin.']],
            ], 422);
        }

        $post = Post::create([
            'user_id' => $request->user()->id,
            'content' => $data['content'],
            'type' => $data['type'],
            'book_id' => $data['book_id'] ?? null,
        ]);

        $post->load(['user', 'book']);

        return response()->json(['data' => $post], 201);
    }

    #[OA\Post(
        path: '/posts/{post}/like',
        summary: 'Beğeni aç/kapa',
        security: [['sanctum' => []]],
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(name: 'post', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Beğeni durumu'),
            new OA\Response(response: 401, description: 'Yetkisiz'),
        ]
    )]
    public function toggleLike(Request $request, Post $post): JsonResponse
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

        return response()->json([
            'liked' => $liked,
            'likes_count' => $post->likes()->count(),
        ]);
    }

    #[OA\Delete(
        path: '/posts/{post}',
        summary: 'Paylaşımı sil',
        security: [['sanctum' => []]],
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(name: 'post', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Silindi'),
            new OA\Response(response: 403, description: 'Yetkisiz'),
        ]
    )]
    public function destroy(Request $request, Post $post): JsonResponse
    {
        if ($post->user_id !== $request->user()->id) {
            abort(403);
        }

        $post->delete();

        return response()->json(['message' => 'Paylaşım silindi.']);
    }
}
