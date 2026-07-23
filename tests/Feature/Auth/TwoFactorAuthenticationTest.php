<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_redirects_to_two_factor_challenge_when_enabled(): void
    {
        $twoFactor = app(TwoFactorService::class);
        $secret = $twoFactor->generateSecret();

        $user = User::factory()->create([
            'password' => 'password',
            'two_factor_secret' => $secret,
            'two_factor_enabled' => true,
            'two_factor_recovery_codes' => $twoFactor->hashRecoveryCodes(['abcdef1234']),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertRedirect(route('two-factor.login'));

        $this->assertGuest();
        $this->assertTrue(session()->has('login.id'));
    }

    public function test_user_can_complete_login_with_totp_code(): void
    {
        $twoFactor = app(TwoFactorService::class);
        $secret = $twoFactor->generateSecret();
        $code = $twoFactor->currentCode($secret);

        $user = User::factory()->create([
            'password' => 'password',
            'two_factor_secret' => $secret,
            'two_factor_enabled' => true,
            'two_factor_recovery_codes' => [],
        ]);

        $this->withSession(['login.id' => $user->id, 'login.remember' => false])
            ->post(route('two-factor.login.store'), ['code' => $code])
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_enable_two_factor_from_settings(): void
    {
        $user = User::factory()->create();
        $twoFactor = app(TwoFactorService::class);

        $this->actingAs($user)
            ->postJson(route('two-factor.setup'))
            ->assertOk()
            ->assertJsonStructure(['secret', 'qr_svg']);

        $secret = session('two_factor_setup_secret');
        $this->assertNotEmpty($secret);

        $code = $twoFactor->currentCode($secret);

        $this->actingAs($user)
            ->postJson(route('two-factor.confirm'), ['code' => $code])
            ->assertOk()
            ->assertJsonPath('enabled', true)
            ->assertJsonStructure(['recovery_codes']);

        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_user_can_disable_two_factor_with_password_and_code(): void
    {
        $twoFactor = app(TwoFactorService::class);
        $secret = $twoFactor->generateSecret();
        $code = $twoFactor->currentCode($secret);

        $user = User::factory()->create([
            'password' => 'password',
            'two_factor_secret' => $secret,
            'two_factor_enabled' => true,
            'two_factor_recovery_codes' => [],
        ]);

        $this->actingAs($user)
            ->postJson(route('two-factor.disable'), [
                'password' => 'password',
                'code' => $code,
            ])
            ->assertOk()
            ->assertJsonPath('enabled', false);

        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_api_login_requires_two_factor_when_enabled(): void
    {
        $twoFactor = app(TwoFactorService::class);
        $secret = $twoFactor->generateSecret();

        $user = User::factory()->create([
            'password' => 'password',
            'two_factor_secret' => $secret,
            'two_factor_enabled' => true,
            'two_factor_recovery_codes' => [],
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('two_factor_required', true)
            ->assertJsonStructure(['two_factor_token']);

        $token = $response->json('two_factor_token');
        $code = $twoFactor->currentCode($secret);

        $this->postJson('/api/login/two-factor', [
            'two_factor_token' => $token,
            'code' => $code,
        ])
            ->assertOk()
            ->assertJsonPath('two_factor_required', false)
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_api_login_accepts_recovery_code(): void
    {
        $twoFactor = app(TwoFactorService::class);
        $secret = $twoFactor->generateSecret();
        $plain = 'recover999';

        $user = User::factory()->create([
            'password' => 'password',
            'two_factor_secret' => $secret,
            'two_factor_enabled' => true,
            'two_factor_recovery_codes' => $twoFactor->hashRecoveryCodes([$plain]),
        ]);

        $pending = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->json('two_factor_token');

        $this->postJson('/api/login/two-factor', [
            'two_factor_token' => $pending,
            'code' => $plain,
        ])
            ->assertOk()
            ->assertJsonStructure(['token']);

        $this->assertSame([], $user->fresh()->two_factor_recovery_codes);
    }
}
