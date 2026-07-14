<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileFollowerCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_own_profile_shows_clickable_follower_list(): void
    {
        $user = User::factory()->create();
        $follower = User::factory()->create(['name' => 'Takipci Ali']);

        $follower->follow($user);

        $this->actingAs($user)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('openFollowPanel(\'followers\')', false)
            ->assertSee('Takipci Ali');
    }
}
