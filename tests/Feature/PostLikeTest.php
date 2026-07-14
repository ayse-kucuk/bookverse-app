<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostLikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_like_a_post_via_json(): void
    {
        $author = User::factory()->create();
        $liker = User::factory()->create();
        $post = Post::factory()->for($author)->create();

        $this->actingAs($liker)
            ->postJson(route('posts.like.toggle', $post))
            ->assertOk()
            ->assertJson([
                'liked' => true,
                'likes_count' => 1,
            ]);
    }

    public function test_authenticated_user_can_like_a_post(): void
    {
        $author = User::factory()->create();
        $liker = User::factory()->create();
        $post = Post::factory()->for($author)->create();

        $this->actingAs($liker)
            ->post(route('posts.like.toggle', $post))
            ->assertRedirect();

        $this->assertDatabaseHas('post_likes', [
            'user_id' => $liker->id,
            'post_id' => $post->id,
        ]);
    }

    public function test_liking_again_removes_the_like(): void
    {
        $author = User::factory()->create();
        $liker = User::factory()->create();
        $post = Post::factory()->for($author)->create();

        PostLike::create([
            'user_id' => $liker->id,
            'post_id' => $post->id,
        ]);

        $this->actingAs($liker)
            ->post(route('posts.like.toggle', $post))
            ->assertRedirect();

        $this->assertDatabaseMissing('post_likes', [
            'user_id' => $liker->id,
            'post_id' => $post->id,
        ]);
    }

    public function test_guest_cannot_like_a_post(): void
    {
        $post = Post::factory()->create();

        $this->post(route('posts.like.toggle', $post))
            ->assertRedirect(route('login'));
    }

    public function test_feed_shows_like_count(): void
    {
        $author = User::factory()->create(['account_visibility' => User::VISIBILITY_PUBLIC]);
        $liker = User::factory()->create();
        $post = Post::factory()->for($author)->create(['content' => 'Begeni testi icerik']);

        PostLike::create([
            'user_id' => $liker->id,
            'post_id' => $post->id,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Begeni testi icerik')
            ->assertSee('1');
    }
}
