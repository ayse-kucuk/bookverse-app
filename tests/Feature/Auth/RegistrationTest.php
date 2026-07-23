<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_registration_with_two_factor_redirects_to_setup(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test2fa@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'enable_two_factor' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('two-factor.setup'));
        $this->assertTrue(session('two_factor_onboarding'));
    }

    public function test_user_can_skip_two_factor_setup_during_onboarding(): void
    {
        $this->post('/register', [
            'name' => 'Skip User',
            'email' => 'skip2fa@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'enable_two_factor' => '1',
        ]);

        $this->post(route('two-factor.setup.skip'))
            ->assertRedirect('/');

        $this->assertFalse(auth()->user()->fresh()->hasTwoFactorEnabled());
        $this->assertFalse(session()->has('two_factor_onboarding'));
    }
}
