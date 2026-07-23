<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('login.id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request, TwoFactorService $twoFactor): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $userId = $request->session()->get('login.id');

        if (! $userId) {
            return redirect()->route('login');
        }

        /** @var User|null $user */
        $user = User::find($userId);

        if (! $user || ! $user->hasTwoFactorEnabled()) {
            $request->session()->forget(['login.id', 'login.remember']);

            return redirect()->route('login');
        }

        $code = trim((string) $request->input('code'));
        $valid = $twoFactor->verify((string) $user->two_factor_secret, $code);

        if (! $valid) {
            $remaining = $twoFactor->consumeRecoveryCode(
                $user->two_factor_recovery_codes ?? [],
                $code
            );

            if ($remaining === null) {
                throw ValidationException::withMessages([
                    'code' => 'Doğrulama kodu geçersiz.',
                ]);
            }

            $user->forceFill([
                'two_factor_recovery_codes' => $remaining,
            ])->save();

            $valid = true;
        }

        if (! $valid) {
            throw ValidationException::withMessages([
                'code' => 'Doğrulama kodu geçersiz.',
            ]);
        }

        Auth::login($user, (bool) $request->session()->pull('login.remember'));
        $request->session()->forget('login.id');
        $request->session()->regenerate();

        if ($user->is_admin) {
            return redirect()->intended(route('home', absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
