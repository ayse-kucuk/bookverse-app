<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_follow_creates_notification(): void
    {
        $follower = User::factory()->create();
        $author = User::factory()->create(['account_visibility' => User::VISIBILITY_PUBLIC]);

        $this->actingAs($follower)
            ->post(route('users.follow', $author))
            ->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $author->id,
            'actor_id' => $follower->id,
            'type' => Notification::TYPE_FOLLOW,
        ]);
    }

    public function test_like_creates_notification_for_post_owner(): void
    {
        $author = User::factory()->create();
        $liker = User::factory()->create();
        $post = Post::factory()->for($author)->create();

        $this->actingAs($liker)
            ->postJson(route('posts.like.toggle', $post))
            ->assertOk();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $author->id,
            'actor_id' => $liker->id,
            'type' => Notification::TYPE_POST_LIKE,
            'post_id' => $post->id,
        ]);
    }

    public function test_unlike_removes_notification(): void
    {
        $author = User::factory()->create();
        $liker = User::factory()->create();
        $post = Post::factory()->for($author)->create();

        $this->actingAs($liker)->postJson(route('posts.like.toggle', $post));
        $this->actingAs($liker)->postJson(route('posts.like.toggle', $post));

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $author->id,
            'actor_id' => $liker->id,
            'type' => Notification::TYPE_POST_LIKE,
            'post_id' => $post->id,
        ]);
    }

    public function test_user_does_not_get_notification_for_own_like(): void
    {
        $author = User::factory()->create();
        $post = Post::factory()->for($author)->create();

        $this->actingAs($author)
            ->postJson(route('posts.like.toggle', $post))
            ->assertOk();

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_notifications_page_requires_auth(): void
    {
        $this->get(route('notifications.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();
        $actor = User::factory()->create();

        $notification = Notification::create([
            'user_id' => $user->id,
            'actor_id' => $actor->id,
            'type' => Notification::TYPE_FOLLOW,
        ]);

        $this->actingAs($user)
            ->get(route('notifications.open', $notification))
            ->assertRedirect(route('users.show', $actor));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_notifications_page_lists_notifications(): void
    {
        $user = User::factory()->create();
        $actor = User::factory()->create();

        Notification::create([
            'user_id' => $user->id,
            'actor_id' => $actor->id,
            'type' => Notification::TYPE_FOLLOW,
        ]);

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee($actor->name);
    }

    public function test_nav_dropdown_only_shows_unread_notifications(): void
    {
        $user = User::factory()->create();
        $actor = User::factory()->create(['name' => 'Okunmamis Bildirim']);
        $readActor = User::factory()->create(['name' => 'Okunmus Bildirim']);

        Notification::create([
            'user_id' => $user->id,
            'actor_id' => $actor->id,
            'type' => Notification::TYPE_FOLLOW,
        ]);

        Notification::create([
            'user_id' => $user->id,
            'actor_id' => $readActor->id,
            'type' => Notification::TYPE_FOLLOW,
            'read_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Okunmamis Bildirim')
            ->assertDontSee('Okunmus Bildirim');
    }

    public function test_mark_notification_read_via_post(): void
    {
        $user = User::factory()->create();
        $actor = User::factory()->create();

        $notification = Notification::create([
            'user_id' => $user->id,
            'actor_id' => $actor->id,
            'type' => Notification::TYPE_FOLLOW,
        ]);

        $this->actingAs($user)
            ->postJson(route('notifications.read', $notification))
            ->assertOk()
            ->assertJson([
                'unread_count' => 0,
                'redirect' => route('users.show', $actor),
            ]);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_read_all_returns_json_for_ajax(): void
    {
        $user = User::factory()->create();
        $actor = User::factory()->create();

        Notification::create([
            'user_id' => $user->id,
            'actor_id' => $actor->id,
            'type' => Notification::TYPE_FOLLOW,
        ]);

        $this->actingAs($user)
            ->postJson(route('notifications.read-all'))
            ->assertOk()
            ->assertJson(['unread_count' => 0]);
    }
}
