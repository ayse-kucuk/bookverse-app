<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    public function __construct(
        private Google2FA $google2fa = new Google2FA()
    ) {}

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function qrCodeSvg(User $user, string $secret): string
    {
        $company = config('app.name', 'Bookverse');
        $otpauth = $this->google2fa->getQRCodeUrl($company, $user->email, $secret);

        $writer = new Writer(
            new ImageRenderer(
                new RendererStyle(200),
                new SvgImageBackEnd
            )
        );

        return $writer->writeString($otpauth);
    }

    public function verify(string $secret, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code) ?? '';

        if (! preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        return (bool) $this->google2fa->verifyKey($secret, $code);
    }

    public function currentCode(string $secret): string
    {
        return $this->google2fa->getCurrentOtp($secret);
    }

    /**
     * @return list<string>
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        return Collection::times($count, fn () => Str::lower(Str::random(10)))->all();
    }

    /**
     * @param  list<string>  $plainCodes
     * @return list<string>
     */
    public function hashRecoveryCodes(array $plainCodes): array
    {
        return array_map(fn (string $code) => hash('sha256', $code), $plainCodes);
    }

    /**
     * @param  list<string>  $hashedCodes
     * @return list<string>|null  Updated hashed list, or null if unused
     */
    public function consumeRecoveryCode(array $hashedCodes, string $plainCode): ?array
    {
        $plainCode = Str::lower(trim($plainCode));
        $hash = hash('sha256', $plainCode);
        $index = array_search($hash, $hashedCodes, true);

        if ($index === false) {
            return null;
        }

        unset($hashedCodes[$index]);

        return array_values($hashedCodes);
    }

    public function issuePendingLoginToken(User $user, bool $remember = false): string
    {
        $token = Str::random(64);

        Cache::put($this->pendingCacheKey($token), [
            'user_id' => $user->id,
            'remember' => $remember,
        ], now()->addMinutes(10));

        return $token;
    }

    /**
     * @return array{user_id: int, remember: bool}|null
     */
    public function pullPendingLogin(string $token): ?array
    {
        $key = $this->pendingCacheKey($token);
        $payload = Cache::pull($key);

        if (! is_array($payload) || ! isset($payload['user_id'])) {
            return null;
        }

        return [
            'user_id' => (int) $payload['user_id'],
            'remember' => (bool) ($payload['remember'] ?? false),
        ];
    }

    public function peekPendingLogin(string $token): ?array
    {
        $payload = Cache::get($this->pendingCacheKey($token));

        if (! is_array($payload) || ! isset($payload['user_id'])) {
            return null;
        }

        return [
            'user_id' => (int) $payload['user_id'],
            'remember' => (bool) ($payload['remember'] ?? false),
        ];
    }

    private function pendingCacheKey(string $token): string
    {
        return 'two_factor_pending:'.$token;
    }
}
