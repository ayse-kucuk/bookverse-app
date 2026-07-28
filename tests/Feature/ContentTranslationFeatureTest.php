<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ContentTranslationFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_english_feed_shows_translated_post_content(): void
    {
        config([
            'services.grok.key' => 'test-grok-key',
            'services.gemini.key' => '',
        ]);

        Http::fake([
            'https://api.x.ai/v1/responses' => Http::response([
                'output' => [[
                    'type' => 'message',
                    'role' => 'assistant',
                    'content' => [[
                        'type' => 'output_text',
                        'text' => '["This is an English post"]',
                    ]],
                ]],
            ], 200),
        ]);

        $author = User::factory()->create(['account_visibility' => User::VISIBILITY_PUBLIC]);
        Post::factory()->for($author)->create(['content' => 'Bu Türkçe bir paylaşım']);

        $this->withSession(['locale' => 'en'])
            ->get(route('home'))
            ->assertOk()
            ->assertSee('This is an English post', false)
            ->assertDontSee('Bu Türkçe bir paylaşım', false);
    }
}
