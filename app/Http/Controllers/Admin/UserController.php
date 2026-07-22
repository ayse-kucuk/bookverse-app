<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->withCount(['posts', 'followers', 'following']);

        if ($search = trim((string) $request->input('q', ''))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->input('role') === 'admin') {
            $query->where('is_admin', true);
        } elseif ($request->input('role') === 'user') {
            $query->where('is_admin', false);
        }

        return view('admin.users.index', [
            'users' => $query->latest()->paginate(20)->withQueryString(),
            'search' => $search,
            'role' => $request->input('role'),
        ]);
    }

    public function toggleAdmin(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Kendi admin yetkini kaldıramazsın.');
        }

        $user->update([
            'is_admin' => ! $user->is_admin,
        ]);

        $label = $user->is_admin ? 'admin yapıldı' : 'admin yetkisi kaldırıldı';

        return back()->with('success', "{$user->name} {$label}.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Kendi hesabını buradan silemezsin.');
        }

        if ($user->is_admin) {
            return back()->with('error', 'Admin kullanıcıyı silmeden önce admin yetkisini kaldır.');
        }

        $user->delete();

        return back()->with('success', 'Kullanıcı silindi.');
    }
}
