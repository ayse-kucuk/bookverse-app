<?php

namespace App\Services;

use App\Models\ContentTranslation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContentTranslationService
{
    private const CHUNK_SIZE = 12;

    public function isActive(): bool
    {
        return app()->getLocale() === 'en';
    }

    public function translate(?string $text, string $sourceLocale = 'tr', string $targetLocale = 'en', string $context = 'general'): string
    {
        if ($text === null || trim($text) === '' || ! $this->isActive()) {
            return $text ?? '';
        }

        $cached = $this->findCached($text, $sourceLocale, $targetLocale);

        if ($cached !== null) {
            return $cached;
        }

        $this->warm([$text], $sourceLocale, $targetLocale, $context);

        return $this->findCached($text, $sourceLocale, $targetLocale) ?? $text;
    }

    /**
     * @param  array<int, string|null>  $texts
     */
    public function warm(array $texts, string $sourceLocale = 'tr', string $targetLocale = 'en', string $context = 'general'): void
    {
        if (! $this->isActive()) {
            return;
        }

        $texts = array_values(array_unique(array_filter(
            array_map(static fn ($text) => is_string($text) ? trim($text) : '', $texts),
            static fn (string $text) => $text !== ''
        )));

        if ($texts === []) {
            return;
        }

        $pending = [];

        foreach ($texts as $text) {
            if ($this->findCached($text, $sourceLocale, $targetLocale) === null) {
                $pending[] = $text;
            }
        }

        if ($pending === []) {
            return;
        }

        foreach (array_chunk($pending, self::CHUNK_SIZE) as $chunk) {
            $translated = $this->translateBatchViaApi($chunk, $sourceLocale, $targetLocale, $context);

            foreach ($chunk as $index => $original) {
                $this->storeCache(
                    $original,
                    $sourceLocale,
                    $targetLocale,
                    $translated[$index] ?? $original,
                    $context
                );
            }
        }
    }

    public function findCached(string $text, string $sourceLocale, string $targetLocale): ?string
    {
        $row = ContentTranslation::query()
            ->where('source_hash', $this->hash($text))
            ->where('source_locale', $sourceLocale)
            ->where('target_locale', $targetLocale)
            ->first();

        return $row?->translated_text;
    }

    private function storeCache(
        string $source,
        string $sourceLocale,
        string $targetLocale,
        string $translated,
        string $context,
    ): void {
        ContentTranslation::query()->updateOrCreate(
            [
                'source_hash' => $this->hash($source),
                'source_locale' => $sourceLocale,
                'target_locale' => $targetLocale,
            ],
            [
                'source_text' => $source,
                'translated_text' => $translated,
                'context' => $context,
            ]
        );
    }

    /**
     * @param  array<int, string>  $texts
     * @return array<int, string>
     */
    private function translateBatchViaApi(array $texts, string $sourceLocale, string $targetLocale, string $context): array
    {
        $grokKey = trim((string) config('services.grok.key', ''));

        if ($grokKey !== '') {
            $result = $this->callGrok($texts, $sourceLocale, $targetLocale, $context, $grokKey);

            if ($result !== null) {
                return $result;
            }
        }

        $geminiKey = trim((string) config('services.gemini.key', ''));

        if ($geminiKey !== '') {
            $result = $this->callGemini($texts, $sourceLocale, $targetLocale, $context, $geminiKey);

            if ($result !== null) {
                return $result;
            }
        }

        Log::warning('Content translation skipped: no XAI_API_KEY or GEMINI_API_KEY configured.');

        return $texts;
    }

    /**
     * @param  array<int, string>  $texts
     * @return array<int, string>|null
     */
    private function callGrok(array $texts, string $sourceLocale, string $targetLocale, string $context, string $apiKey): ?array
    {
        $baseUrl = rtrim((string) config('services.grok.base_url', 'https://api.x.ai/v1'), '/');
        $prompt = $this->systemPrompt($sourceLocale, $targetLocale, $context)
            ."\n\n"
            .json_encode(array_values($texts), JSON_UNESCAPED_UNICODE);

        try {
            $response = Http::withToken($apiKey)
                ->timeout(90)
                ->acceptJson()
                ->post("{$baseUrl}/responses", [
                    'model' => config('services.grok.model', 'grok-4.5'),
                    'input' => $prompt,
                ]);

            if ($response->failed()) {
                Log::warning('Grok translation failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $content = $this->extractGrokResponseText($response->json());

            return $this->parseTranslationArray($content, count($texts), $texts);
        } catch (\Throwable $e) {
            Log::warning('Grok translation exception.', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    private function extractGrokResponseText(?array $payload): ?string
    {
        if (! is_array($payload)) {
            return null;
        }

        foreach (data_get($payload, 'output', []) as $item) {
            if (! is_array($item)) {
                continue;
            }

            foreach (data_get($item, 'content', []) as $content) {
                if (! is_array($content)) {
                    continue;
                }

                if (($content['type'] ?? null) === 'output_text' && is_string($content['text'] ?? null)) {
                    return $content['text'];
                }
            }
        }

        return data_get($payload, 'output_text');
    }

    /**
     * @param  array<int, string>  $texts
     * @return array<int, string>|null
     */
    private function callGemini(array $texts, string $sourceLocale, string $targetLocale, string $context, string $apiKey): ?array
    {
        $model = (string) config('services.gemini.model', 'gemini-flash-latest');
        $prompt = $this->systemPrompt($sourceLocale, $targetLocale, $context)."\n\n".json_encode(array_values($texts), JSON_UNESCAPED_UNICODE);

        try {
            $response = Http::timeout(90)
                ->acceptJson()
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.1,
                    ],
                ]);

            if ($response->failed()) {
                Log::warning('Gemini translation fallback failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $content = data_get($response->json(), 'candidates.0.content.parts.0.text');

            return $this->parseTranslationArray($content, count($texts), $texts);
        } catch (\Throwable $e) {
            Log::warning('Gemini translation exception.', ['error' => $e->getMessage()]);

            return null;
        }
    }

    private function systemPrompt(string $sourceLocale, string $targetLocale, string $context): string
    {
        return implode("\n", [
            'You are a professional translator for a book social network (Bookverse).',
            "Translate each string from {$sourceLocale} to {$targetLocale}.",
            "Context: {$context}.",
            'Rules:',
            '- Return ONLY a valid JSON array of translated strings.',
            '- Keep the same number of items and the same order as the input.',
            '- Preserve book titles, author names, usernames, emojis, and line breaks.',
            '- Do not add explanations or markdown.',
            '- If a string is already in the target language, return it unchanged.',
        ]);
    }

    /**
     * @param  array<int, string>  $fallback
     * @return array<int, string>|null
     */
    private function parseTranslationArray(?string $content, int $expectedCount, array $fallback): ?array
    {
        if (! is_string($content) || trim($content) === '') {
            return null;
        }

        $content = trim($content);
        $content = preg_replace('/^```(?:json)?\s*|\s*```$/u', '', $content) ?? $content;

        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            return null;
        }

        $decoded = array_values(array_map(static fn ($item) => is_string($item) ? trim($item) : '', $decoded));

        if (count($decoded) !== $expectedCount) {
            return null;
        }

        foreach ($decoded as $index => $line) {
            if ($line === '') {
                $decoded[$index] = $fallback[$index];
            }
        }

        return $decoded;
    }

    private function hash(string $text): string
    {
        return hash('sha256', $text);
    }
}
