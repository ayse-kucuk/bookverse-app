<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_public_posts(): void
    {
        $author = User::factory()->create(['account_visibility' => User::VISIBILITY_PUBLIC]);
        Post::factory()->for($author)->create(['content' => 'Herkese acik paylasim']);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Herkese acik paylasim');
    }

    public function test_followers_only_posts_are_hidden_from_guests(): void
    {
        $author = User::factory()->create(['account_visibility' => User::VISIBILITY_FOLLOWERS]);
        Post::factory()->for($author)->create(['content' => 'Gizli paylasim']);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('Gizli paylasim');
    }

    public function test_user_can_follow_and_see_followers_only_posts(): void
    {
        $author = User::factory()->create(['account_visibility' => User::VISIBILITY_FOLLOWERS]);
        $follower = User::factory()->create();
        Post::factory()->for($author)->create(['content' => 'Takipcilere ozel']);

        $follower->follow($author);

        $response = $this->actingAs($follower)->get(route('home'));

        $response->assertOk();
        $response->assertSee('Takipcilere ozel');
    }

    public function test_authenticated_user_can_create_post(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('posts.store'), [
            'content' => 'Yeni dusuncem',
            'type' => 'thought',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'content' => 'Yeni dusuncem',
            'type' => 'thought',
        ]);
    }

    public function test_private_profile_is_blocked_for_non_followers(): void
    {
        $author = User::factory()->create(['account_visibility' => User::VISIBILITY_FOLLOWERS]);
        $stranger = User::factory()->create();

        $response = $this->actingAs($stranger)->get(route('users.show', $author));

        $response->assertForbidden();
    }
}
