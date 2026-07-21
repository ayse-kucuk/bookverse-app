<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class NotificationController extends Controller
{
    #[OA\Get(
        path: '/notifications',
        summary: 'Bildirim listesi',
        security: [['sanctum' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Bildirimler'),
            new OA\Response(response: 401, description: 'Yetkisiz'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->with(['actor', 'post'])
            ->latest()
            ->paginate(20);

        return response()->json($notifications);
    }

    #[OA\Post(
        path: '/notifications/read-all',
        summary: 'Tüm bildirimleri okundu işaretle',
        security: [['sanctum' => []]],
        tags: ['Notifications'],
        responses: [
            new OA\Response(response: 200, description: 'Okundu'),
            new OA\Response(response: 401, description: 'Yetkisiz'),
        ]
    )]
    public function readAll(Request $request): JsonResponse
    {
        $request->user()
            ->notifications()
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['unread_count' => 0]);
    }

    #[OA\Post(
        path: '/notifications/{notification}/read',
        summary: 'Bildirimi okundu işaretle',
        security: [['sanctum' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'notification', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Okundu'),
            new OA\Response(response: 403, description: 'Yetkisiz'),
        ]
    )]
    public function read(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403);
        }

        $notification->markAsRead();

        return response()->json([
            'redirect' => $notification->url(),
            'unread_count' => $request->user()->unreadNotificationsCount(),
        ]);
    }
}
