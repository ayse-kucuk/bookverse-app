<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('account-settings', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->safe()->except(['profile_photo']);

        if ($request->hasFile('profile_photo')) {
            $disk = User::profilePhotosDisk();

            try {
                if ($user->profile_photo_path
                    && ! str_starts_with($user->profile_photo_path, 'http://')
                    && ! str_starts_with($user->profile_photo_path, 'https://')) {
                    Storage::disk($disk)->delete($user->profile_photo_path);
                }

                $path = $request->file('profile_photo')->store(
                    'avatars',
                    [
                        'disk' => $disk,
                        'visibility' => 'public',
                    ]
                );

                // On cloud disks store a full public URL so images keep working
                // even if the server filesystem is ephemeral (Render).
                if ($disk !== 'public') {
                    $base = rtrim((string) config("filesystems.disks.{$disk}.url"), '/');
                    $data['profile_photo_path'] = $base !== ''
                        ? $base.'/'.ltrim($path, '/')
                        : Storage::disk($disk)->url($path);
                } else {
                    $data['profile_photo_path'] = $path;
                }
            } catch (\Throwable $e) {
                report($e);

                return Redirect::route('account.settings')
                    ->withInput()
                    ->withErrors([
                        'profile_photo' => 'Profil fotoğrafı yüklenemedi. Canlı ortamda Supabase Storage ayarlarını kontrol et.',
                    ]);
            }
        }

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('account.settings')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
