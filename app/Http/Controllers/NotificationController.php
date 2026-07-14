<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->with(['actor', 'post'])
            ->latest()
            ->paginate(20);

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    public function open(Request $request, Notification $notification): RedirectResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403);
        }

        $notification->markAsRead();

        return redirect($notification->url());
    }

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

    public function readAll(Request $request): RedirectResponse|JsonResponse
    {
        $request->user()
            ->notifications()
            ->unread()
            ->update(['read_at' => now()]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', 'Tüm bildirimler okundu olarak işaretlendi.');
    }
}
