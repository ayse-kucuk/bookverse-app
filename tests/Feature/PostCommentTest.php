<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_comment_on_post(): void
    {
        $author = User::factory()->create(['account_visibility' => User::VISIBILITY_PUBLIC]);
        $commenter = User::factory()->create();
        $post = Post::factory()->for($author)->create(['content' => 'Yorumlanacak paylasim']);

        $this->actingAs($commenter)
            ->post(route('posts.comments.store', $post), [
                'content' => 'Harika bir paylasim!',
            ])
            ->assertRedirect(route('posts.show', $post).'#comments')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('post_comments', [
            'post_id' => $post->id,
            'user_id' => $commenter->id,
            'content' => 'Harika bir paylasim!',
        ]);
    }

    public function test_comment_creates_notification_for_post_owner(): void
    {
        $author = User::factory()->create();
        $commenter = User::factory()->create();
        $post = Post::factory()->for($author)->create();

        $this->actingAs($commenter)
            ->post(route('posts.comments.store', $post), [
                'content' => 'Cok guzel!',
            ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $author->id,
            'actor_id' => $commenter->id,
            'type' => Notification::TYPE_POST_COMMENT,
            'post_id' => $post->id,
        ]);
    }

    public function test_self_comment_does_not_create_notification(): void
    {
        $author = User::factory()->create();
        $post = Post::factory()->for($author)->create();

        $this->actingAs($author)
            ->post(route('posts.comments.store', $post), [
                'content' => 'Kendi yorumum',
            ]);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $author->id,
            'actor_id' => $author->id,
            'type' => Notification::TYPE_POST_COMMENT,
        ]);
    }

    public function test_comment_author_can_delete_own_comment(): void
    {
        $author = User::factory()->create();
        $commenter = User::factory()->create();
        $post = Post::factory()->for($author)->create();
        $comment = PostComment::factory()->for($post)->for($commenter)->create();

        $this->actingAs($commenter)
            ->delete(route('posts.comments.destroy', [$post, $comment]))
            ->assertRedirect();

        $this->assertDatabaseMissing('post_comments', ['id' => $comment->id]);
    }

    public function test_post_owner_can_delete_comment_on_their_post(): void
    {
        $author = User::factory()->create();
        $commenter = User::factory()->create();
        $post = Post::factory()->for($author)->create();
        $comment = PostComment::factory()->for($post)->for($commenter)->create();

        $this->actingAs($author)
            ->delete(route('posts.comments.destroy', [$post, $comment]))
            ->assertRedirect();

        $this->assertDatabaseMissing('post_comments', ['id' => $comment->id]);
    }

    public function test_other_user_cannot_delete_comment(): void
    {
        $author = User::factory()->create();
        $commenter = User::factory()->create();
        $stranger = User::factory()->create();
        $post = Post::factory()->for($author)->create();
        $comment = PostComment::factory()->for($post)->for($commenter)->create();

        $this->actingAs($stranger)
            ->delete(route('posts.comments.destroy', [$post, $comment]))
            ->assertForbidden();

        $this->assertDatabaseHas('post_comments', ['id' => $comment->id]);
    }

    public function test_guest_cannot_comment(): void
    {
        $post = Post::factory()->create();

        $this->post(route('posts.comments.store', $post), [
            'content' => 'Misafir yorumu',
        ])->assertRedirect(route('login'));
    }

    public function test_post_show_displays_comments(): void
    {
        $author = User::factory()->create(['account_visibility' => User::VISIBILITY_PUBLIC]);
        $post = Post::factory()->for($author)->create();
        PostComment::factory()->for($post)->create(['content' => 'Gorunur yorum metni']);

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('Gorunur yorum metni')
            ->assertSee('Yorumlar');
    }

    public function test_api_user_can_create_comment(): void
    {
        $author = User::factory()->create();
        $commenter = User::factory()->create();
        $post = Post::factory()->for($author)->create();

        $this->actingAs($commenter, 'sanctum')
            ->postJson("/api/posts/{$post->id}/comments", [
                'content' => 'API yorumu',
            ])
            ->assertCreated()
            ->assertJsonPath('data.content', 'API yorumu')
            ->assertJsonPath('comments_count', 1);
    }

    public function test_feed_shows_comment_count_link(): void
    {
        $author = User::factory()->create(['account_visibility' => User::VISIBILITY_PUBLIC]);
        $post = Post::factory()->for($author)->create(['content' => 'Yorum sayisi testi']);
        PostComment::factory()->count(2)->for($post)->create();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Yorum sayisi testi')
            ->assertSee('2');
    }
}
