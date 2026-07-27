<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function store(Request $request, User $user): RedirectResponse
    {
        $follower = $request->user();

        if ($follower->id === $user->id) {
            return back()->with('error', __('ui.profile.cannot_follow_self'));
        }

        $follower->follow($user);

        Notification::recordFollow($user, $follower);

        return back()->with('success', __('ui.profile.followed', ['name' => $user->name]));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $request->user()->unfollow($user);

        return back()->with('success', __('ui.profile.unfollowed', ['name' => $user->name]));
    }
}
