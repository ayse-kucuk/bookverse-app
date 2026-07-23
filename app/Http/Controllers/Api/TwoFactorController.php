<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class TwoFactorController extends Controller
{
    #[OA\Get(
        path: '/two-factor',
        summary: '2FA durumu',
        security: [['sanctum' => []]],
        tags: ['Two-Factor'],
        responses: [new OA\Response(response: 200, description: '2FA durumu')]
    )]
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'enabled' => $request->user()->hasTwoFactorEnabled(),
        ]);
    }

    #[OA\Post(
        path: '/two-factor/setup',
        summary: '2FA kurulumu başlat (secret + QR)',
        security: [['sanctum' => []]],
        tags: ['Two-Factor'],
        responses: [new OA\Response(response: 200, description: 'Kurulum bilgisi')]
    )]
    public function setup(Request $request, TwoFactorService $twoFactor): JsonResponse
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            throw ValidationException::withMessages([
                'two_factor' => ['Çift aşamalı doğrulama zaten aktif.'],
            ]);
        }

        $secret = $twoFactor->generateSecret();
        $request->session()->put('two_factor_setup_secret', $secret);
        // For token-based API clients without session, stash by user id as fallback
        cache()->put($this->setupCacheKey($user->id), $secret, now()->addMinutes(15));

        return response()->json([
            'secret' => $secret,
            'qr_svg' => $twoFactor->qrCodeSvg($user, $secret),
        ]);
    }

    #[OA\Post(
        path: '/two-factor/confirm',
        summary: '6 haneli kod ile 2FA aktifleştir',
        security: [['sanctum' => []]],
        tags: ['Two-Factor'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['code'],
                properties: [new OA\Property(property: 'code', type: 'string', example: '123456')]
            )
        ),
        responses: [new OA\Response(response: 200, description: '2FA etkinleştirildi')]
    )]
    public function confirm(Request $request, TwoFactorService $twoFactor): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = $request->user();
        $secret = $request->session()->get('two_factor_setup_secret')
            ?? cache()->get($this->setupCacheKey($user->id));

        if (! $secret) {
            throw ValidationException::withMessages([
                'code' => ['Kurulum oturumu bulunamadı. Lütfen tekrar başlatın.'],
            ]);
        }

        if (! $twoFactor->verify($secret, (string) $request->input('code'))) {
            throw ValidationException::withMessages([
                'code' => ['Doğrulama kodu geçersiz.'],
            ]);
        }

        $plainCodes = $twoFactor->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_enabled' => true,
            'two_factor_recovery_codes' => $twoFactor->hashRecoveryCodes($plainCodes),
        ])->save();

        $request->session()->forget('two_factor_setup_secret');
        cache()->forget($this->setupCacheKey($user->id));

        return response()->json([
            'enabled' => true,
            'recovery_codes' => $plainCodes,
            'message' => 'Çift aşamalı doğrulama etkinleştirildi.',
        ]);
    }

    #[OA\Post(
        path: '/two-factor/disable',
        summary: '2FA kapat (şifre + kod)',
        security: [['sanctum' => []]],
        tags: ['Two-Factor'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['password', 'code'],
                properties: [
                    new OA\Property(property: 'password', type: 'string', format: 'password'),
                    new OA\Property(property: 'code', type: 'string', example: '123456'),
                ]
            )
        ),
        responses: [new OA\Response(response: 200, description: '2FA kapatıldı')]
    )]
    public function disable(Request $request, TwoFactorService $twoFactor): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
            'code' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! Hash::check((string) $request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Şifre hatalı.'],
            ]);
        }

        if (! $user->hasTwoFactorEnabled()) {
            throw ValidationException::withMessages([
                'code' => ['Çift aşamalı doğrulama zaten kapalı.'],
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
                    'code' => ['Doğrulama kodu geçersiz.'],
                ]);
            }
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_enabled' => false,
            'two_factor_recovery_codes' => null,
        ])->save();

        return response()->json([
            'enabled' => false,
            'message' => 'Çift aşamalı doğrulama kapatıldı.',
        ]);
    }

    private function setupCacheKey(int $userId): string
    {
        return 'two_factor_setup:'.$userId;
    }
}
