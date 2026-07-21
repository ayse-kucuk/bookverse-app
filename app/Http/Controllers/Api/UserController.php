<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    #[OA\Get(
        path: '/users/{user}',
        summary: 'Kullanıcı profili',
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Profil'),
            new OA\Response(response: 403, description: 'Gizli profil'),
            new OA\Response(response: 404, description: 'Bulunamadı'),
        ]
    )]
    public function show(Request $request, User $user): JsonResponse
    {
        $viewer = $request->user('sanctum');

        if (! $user->canBeViewedBy($viewer)) {
            abort(403, 'Bu profil yalnızca takipçilere açık.');
        }

        $user->loadCount(['followers', 'following', 'posts']);

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'account_visibility' => $user->account_visibility,
                'profile_photo_path' => $user->profile_photo_path,
                'followers_count' => $user->followers_count,
                'following_count' => $user->following_count,
                'posts_count' => $user->posts_count,
                'is_following' => $viewer ? $user->isFollowedBy($viewer) && $viewer->id !== $user->id : false,
            ],
        ]);
    }

    #[OA\Post(
        path: '/users/{user}/follow',
        summary: 'Kullanıcıyı takip et',
        security: [['sanctum' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Takip edildi'),
            new OA\Response(response: 401, description: 'Yetkisiz'),
        ]
    )]
    public function follow(Request $request, User $user): JsonResponse
    {
        $follower = $request->user();

        if ($follower->id === $user->id) {
            return response()->json(['message' => 'Kendini takip edemezsin.'], 422);
        }

        $follower->follow($user);
        Notification::recordFollow($user, $follower);

        return response()->json(['message' => 'Takip edildi.', 'following' => true]);
    }

    #[OA\Delete(
        path: '/users/{user}/follow',
        summary: 'Takipten çık',
        security: [['sanctum' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Takipten çıkıldı'),
            new OA\Response(response: 401, description: 'Yetkisiz'),
        ]
    )]
    public function unfollow(Request $request, User $user): JsonResponse
    {
        $request->user()->unfollow($user);

        return response()->json(['message' => 'Takipten çıkıldı.', 'following' => false]);
    }
}
