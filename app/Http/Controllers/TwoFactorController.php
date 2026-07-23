<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function status(Request $request): View|JsonResponse
    {
        $user = $request->user();

        if ($request->wantsJson()) {
            return response()->json([
                'enabled' => $user->hasTwoFactorEnabled(),
            ]);
        }

        return redirect()->route('account.settings');
    }

    public function setup(Request $request, TwoFactorService $twoFactor): JsonResponse
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            throw ValidationException::withMessages([
                'two_factor' => 'Çift aşamalı doğrulama zaten aktif.',
            ]);
        }

        $secret = $twoFactor->generateSecret();
        $request->session()->put('two_factor_setup_secret', $secret);
        cache()->put('two_factor_setup:'.$user->id, $secret, now()->addMinutes(15));

        return response()->json([
            'secret' => $secret,
            'qr_svg' => $twoFactor->qrCodeSvg($user, $secret),
        ]);
    }

    public function confirm(Request $request, TwoFactorService $twoFactor): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = $request->user();
        $secret = $request->session()->get('two_factor_setup_secret')
            ?? cache()->get('two_factor_setup:'.$user->id);

        if (! $secret) {
            throw ValidationException::withMessages([
                'code' => 'Kurulum oturumu bulunamadı. Lütfen tekrar başlatın.',
            ]);
        }

        if (! $twoFactor->verify($secret, (string) $request->input('code'))) {
            throw ValidationException::withMessages([
                'code' => 'Doğrulama kodu geçersiz.',
            ]);
        }

        $plainCodes = $twoFactor->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_enabled' => true,
            'two_factor_recovery_codes' => $twoFactor->hashRecoveryCodes($plainCodes),
        ])->save();

        $request->session()->forget('two_factor_setup_secret');
        cache()->forget('two_factor_setup:'.$user->id);

        return response()->json([
            'enabled' => true,
            'recovery_codes' => $plainCodes,
            'message' => 'Çift aşamalı doğrulama etkinleştirildi.',
        ]);
    }

    public function disable(Request $request, TwoFactorService $twoFactor): JsonResponse|RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
            'code' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! Hash::check((string) $request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Şifre hatalı.',
            ]);
        }

        if (! $user->hasTwoFactorEnabled()) {
            throw ValidationException::withMessages([
                'code' => 'Çift aşamalı doğrulama zaten kapalı.',
            ]);
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
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_enabled' => false,
            'two_factor_recovery_codes' => null,
        ])->save();

        $request->session()->forget('two_factor_setup_secret');

        if ($request->wantsJson()) {
            return response()->json([
                'enabled' => false,
                'message' => 'Çift aşamalı doğrulama kapatıldı.',
            ]);
        }

        return back()->with('success', 'Çift aşamalı doğrulama kapatıldı.');
    }
}
