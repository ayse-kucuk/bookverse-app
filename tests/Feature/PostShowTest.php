<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_public_users_post(): void
    {
        $author = User::factory()->create(['account_visibility' => User::VISIBILITY_PUBLIC]);
        $post = Post::factory()->for($author)->create(['content' => 'Herkese acik paylasim detayi']);

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('Herkese acik paylasim detayi');
    }

    public function test_like_notification_links_to_post(): void
    {
        $author = User::factory()->create();
        $liker = User::factory()->create();
        $post = Post::factory()->for($author)->create();

        $notification = Notification::create([
            'user_id' => $author->id,
            'actor_id' => $liker->id,
            'type' => Notification::TYPE_POST_LIKE,
            'post_id' => $post->id,
        ]);

        $this->assertEquals(route('posts.show', $post), $notification->url());
    }
}
