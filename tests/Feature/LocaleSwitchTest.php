<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_switch_locale_to_english(): void
    {
        $this->from(route('home'))
            ->get(route('locale.switch', 'en'))
            ->assertRedirect();

        $this->assertSame('en', session('locale'));

        $this->withSession(['locale' => 'en'])
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Feed', false)
            ->assertSee('Explore', false);
    }

    public function test_user_can_switch_locale_to_turkish(): void
    {
        $this->withSession(['locale' => 'en'])
            ->from(route('home'))
            ->get(route('locale.switch', 'tr'))
            ->assertRedirect();

        $this->assertSame('tr', session('locale'));

        $this->withSession(['locale' => 'tr'])
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Akış', false)
            ->assertSee('Keşfet', false);
    }

    public function test_invalid_locale_returns_not_found(): void
    {
        $this->get('/locale/fr')->assertNotFound();
    }
}
