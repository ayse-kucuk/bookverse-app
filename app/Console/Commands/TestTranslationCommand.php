<?php

namespace App\Console\Commands;

use App\Services\ContentTranslationService;
use Illuminate\Console\Command;

class TestTranslationCommand extends Command
{
    protected $signature = 'translation:test {text=Bu hafta sonu harika bir kitap okudum.}';

    protected $description = 'Test Grok/Gemini content translation with real API';

    public function handle(ContentTranslationService $service): int
    {
        app()->setLocale('en');

        $text = (string) $this->argument('text');

        $this->line('Locale: '.app()->getLocale());
        $this->line('Grok key: '.(filled(config('services.grok.key')) ? 'yes' : 'no'));
        $this->line('Gemini key: '.(filled(config('services.gemini.key')) ? 'yes' : 'no'));
        $this->line('Source: '.$text);

        $translated = $service->translate($text, 'tr', 'en', 'post');

        $this->newLine();
        $this->info('Translation: '.$translated);

        return self::SUCCESS;
    }
}
