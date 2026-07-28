<?php

use App\Services\ContentTranslationService;

if (! function_exists('trans_content')) {
    function trans_content(?string $text, string $context = 'general'): string
    {
        return app(ContentTranslationService::class)->translate($text, 'tr', 'en', $context);
    }
}
