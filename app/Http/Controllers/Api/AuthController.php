<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/register',
        summary: 'Yeni kullanıcı kaydı',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Ayşe Yılmaz'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ayse@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'password123'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Kayıt başarılı'),
            new OA\Response(response: 422, description: 'Doğrulama hatası'),
        ]
    )]
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => $this->userPayload($user),
            'token' => $token,
        ], 201);
    }

    #[OA\Post(
        path: '/login',
        summary: 'Giriş yap ve token al (2FA açıksa two_factor_required döner)',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ayse@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Giriş başarılı veya 2FA gerekli'),
            new OA\Response(response: 422, description: 'Geçersiz kimlik bilgileri'),
        ]
    )]
    public function login(Request $request, TwoFactorService $twoFactor): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['E-posta veya şifre hatalı.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        if ($user->hasTwoFactorEnabled()) {
            Auth::logout();

            $pendingToken = $twoFactor->issuePendingLoginToken($user);

            return response()->json([
                'two_factor_required' => true,
                'two_factor_token' => $pendingToken,
                'message' => 'Çift aşamalı doğrulama kodu gerekli.',
            ]);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'two_factor_required' => false,
            'user' => $this->userPayload($user),
            'token' => $token,
        ]);
    }

    #[OA\Post(
        path: '/login/two-factor',
        summary: '2FA kodu ile girişi tamamla',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['two_factor_token', 'code'],
                properties: [
                    new OA\Property(property: 'two_factor_token', type: 'string'),
                    new OA\Property(property: 'code', type: 'string', example: '123456'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Giriş başarılı'),
            new OA\Response(response: 422, description: 'Geçersiz kod veya token'),
        ]
    )]
    public function verifyTwoFactor(Request $request, TwoFactorService $twoFactor): JsonResponse
    {
        $data = $request->validate([
            'two_factor_token' => ['required', 'string'],
            'code' => ['required', 'string'],
        ]);

        $pending = $twoFactor->peekPendingLogin($data['two_factor_token']);

        if (! $pending) {
            throw ValidationException::withMessages([
                'two_factor_token' => ['Doğrulama oturumu süresi dolmuş. Tekrar giriş yapın.'],
            ]);
        }

        /** @var User|null $user */
        $user = User::find($pending['user_id']);

        if (! $user || ! $user->hasTwoFactorEnabled()) {
            throw ValidationException::withMessages([
                'code' => ['Çift aşamalı doğrulama kullanılamıyor.'],
            ]);
        }

        $code = trim($data['code']);
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

            $user->forceFill([
                'two_factor_recovery_codes' => $remaining,
            ])->save();
        }

        $twoFactor->pullPendingLogin($data['two_factor_token']);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'two_factor_required' => false,
            'user' => $this->userPayload($user),
            'token' => $token,
        ]);
    }

    #[OA\Post(
        path: '/logout',
        summary: 'Çıkış yap (token iptal)',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Çıkış başarılı'),
            new OA\Response(response: 401, description: 'Yetkisiz'),
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Çıkış yapıldı.']);
    }

    #[OA\Get(
        path: '/me',
        summary: 'Oturum açmış kullanıcı',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Kullanıcı bilgisi'),
            new OA\Response(response: 401, description: 'Yetkisiz'),
        ]
    )]
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => (bool) $user->is_admin,
            'account_visibility' => $user->account_visibility,
            'profile_photo_path' => $user->profile_photo_path,
            'profile_photo_url' => $user->profilePhotoUrl(),
            'reading_goal' => $user->readingGoalStats(),
            'two_factor_enabled' => $user->hasTwoFactorEnabled(),
        ];
    }
}
