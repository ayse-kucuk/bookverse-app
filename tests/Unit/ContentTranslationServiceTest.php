<?php

namespace Tests\Unit;

use App\Services\ContentTranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ContentTranslationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        config([
            'services.grok.key' => 'test-grok-key',
            'services.grok.model' => 'grok-4.5',
            'services.gemini.key' => '',
        ]);
    }

    public function test_returns_original_text_when_locale_is_turkish(): void
    {
        app()->setLocale('tr');

        $result = app(ContentTranslationService::class)->translate('Merhaba dünya', 'tr', 'en', 'post');

        $this->assertSame('Merhaba dünya', $result);
        Http::assertNothingSent();
    }

    public function test_translates_text_via_grok_and_caches_result(): void
    {
        Http::fake([
            'https://api.x.ai/v1/responses' => Http::response([
                'output' => [[
                    'type' => 'message',
                    'role' => 'assistant',
                    'content' => [[
                        'type' => 'output_text',
                        'text' => '["Hello world"]',
                    ]],
                ]],
            ], 200),
        ]);

        $service = app(ContentTranslationService::class);

        $first = $service->translate('Merhaba dünya', 'tr', 'en', 'post');
        $second = $service->translate('Merhaba dünya', 'tr', 'en', 'post');

        $this->assertSame('Hello world', $first);
        $this->assertSame('Hello world', $second);
        Http::assertSentCount(1);
    }

    public function test_batch_warm_translates_multiple_strings(): void
    {
        Http::fake([
            'https://api.x.ai/v1/responses' => Http::response([
                'output' => [[
                    'type' => 'message',
                    'role' => 'assistant',
                    'content' => [[
                        'type' => 'output_text',
                        'text' => '["Fantasy","Great book"]',
                    ]],
                ]],
            ], 200),
        ]);

        $service = app(ContentTranslationService::class);
        $service->warm(['Fantastik', 'Harika kitap'], 'tr', 'en', 'general');

        $this->assertSame('Fantasy', $service->translate('Fantastik', 'tr', 'en', 'category'));
        $this->assertSame('Great book', $service->translate('Harika kitap', 'tr', 'en', 'post'));
    }
}
