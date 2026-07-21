<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_returns_token(): void
    {
        $user = User::factory()->create([
            'email' => 'api@example.com',
            'password' => 'password123',
        ]);

        $this->postJson('/api/login', [
            'email' => 'api@example.com',
            'password' => 'password123',
        ])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
    }

    public function test_api_books_index(): void
    {
        Book::factory()->create(['title' => 'API Kitabi']);

        $this->getJson('/api/books')
            ->assertOk()
            ->assertJsonFragment(['title' => 'API Kitabi']);
    }

    public function test_api_authenticated_user_can_create_post(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/posts', [
                'content' => 'API uzerinden paylasim',
                'type' => 'thought',
            ])
            ->assertCreated()
            ->assertJsonPath('data.content', 'API uzerinden paylasim');
    }

    public function test_api_me_requires_auth(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }

    public function test_swagger_documentation_is_reachable(): void
    {
        $this->get('/api/documentation')->assertOk();
    }
}
