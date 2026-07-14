<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    public function show(Request $request, User $user): View
    {
        $viewer = $request->user();

        if (! $user->canBeViewedBy($viewer)) {
            abort(403, 'Bu profil yalnızca takipçilere açık.');
        }

        $user->loadCount(['followers', 'following']);
        $followers = $user->followers()->orderByPivot('created_at', 'desc')->get();
        $following = $user->following()->orderByPivot('created_at', 'desc')->get();
        $userBooks = $user->books;
        $posts = $user->posts()
            ->with('book')
            ->withLikeMeta($viewer)
            ->paginate(10);

        return view('users.show', [
            'profileUser' => $user,
            'followers' => $followers,
            'following' => $following,
            'viewer' => $viewer,
            'isFollowing' => $viewer ? $user->isFollowedBy($viewer) && $viewer->id !== $user->id : false,
            'reading' => $userBooks->where('pivot.status', 'okuyorum'),
            'willRead' => $userBooks->where('pivot.status', 'okuyacagim'),
            'read' => $userBooks->where('pivot.status', 'okundu'),
            'posts' => $posts,
        ]);
    }
}
