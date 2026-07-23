<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiRecommendationService
{
    /**
     * @param  array<string, mixed>  $params
     * @param  array<int, array{id?: int, title: string, author: string, category: string}>  $candidateBooks
     * @return array<string, mixed>
     */
    public function recommend(?User $user, array $params, array $candidateBooks): array
    {
        $apiKey = (string) config('services.gemini.key', '');

        if ($apiKey === '') {
            return [
                'recommendations' => [],
                'message' => 'GEMINI_API_KEY eksik.',
                'source' => 'error',
            ];
        }

        if ($candidateBooks === []) {
            return [
                'recommendations' => [],
                'message' => 'Şu an öneri için uygun kitap bulunamadı.',
                'source' => 'empty',
            ];
        }

        $primaryModel = (string) config('services.gemini.model', 'gemini-flash-latest');
        $models = array_values(array_unique(array_filter([
            $primaryModel,
            'gemini-flash-latest',
            'gemini-flash-lite-latest',
            'gemini-2.0-flash',
        ])));

        $limit = max(1, min((int) ($params['limit'] ?? 5), 10));

        $systemPrompt = implode("\n", [
            'You are an expert literature critic and a personalized book recommendation assistant for a Turkish reading community (Bookverse).',
            'You must respond with ONLY valid JSON. No markdown, no code fences, no extra keys.',
            'Return exactly this schema:',
            '{',
            '  "recommendations": [',
            '    {',
            '      "id": 123,',
            '      "title": "Kitap Adı",',
            '      "author": "Yazar",',
            '      "genre": "Tür",',
            '      "matchScore": 95,',
            '      "reason": "Bu kitabı neden sevebileceğine dair 2-3 cümlelik özelleştirilmiş açıklama."',
            '    }',
            '  ]',
            '}',
            '',
            'Rules:',
            '- You MUST recommend ONLY from the provided candidate books list.',
            '- Always include the exact candidate "id", "title", "author", and genre/category values.',
            '- Prefer books that match mood / free_text / genre when possible.',
            '- matchScore must be an integer from 0 to 100.',
            '- reason must be in Turkish and personalized.',
            '- Return at most '.$limit.' recommendations.',
            '- If nothing fits well, still return the best available candidates from the list.',
        ]);

        $userPrompt = implode("\n", [
            'User request:',
            json_encode([
                'mood' => ($params['mood'] ?? null) ?: null,
                'genre' => ($params['genre_name'] ?? null) ?: null,
                'free_text' => ($params['free_text'] ?? null) ?: null,
                'limit' => $limit,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            '',
            'User context from database (may be empty):',
            json_encode($this->buildUserContext($user), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            '',
            'Candidate books (you MUST pick ONLY from these; keep id exact):',
            $this->buildCandidateText($candidateBooks),
        ]);

        $lastStatus = null;
        $lastBody = null;

        foreach ($models as $model) {
            $response = $this->callGemini($apiKey, $model, $systemPrompt, $userPrompt);

            if (! $response instanceof Response) {
                continue;
            }

            $lastStatus = $response->status();
            $lastBody = $response->body();

            if (! $response->successful()) {
                Log::warning('Gemini recommend call failed.', [
                    'model' => $model,
                    'status' => $lastStatus,
                    'body' => $lastBody,
                ]);

                // Kota / model yoksa sonraki modele geç
                if (in_array($lastStatus, [404, 429], true)) {
                    continue;
                }

                break;
            }

            $text = (string) data_get($response->json(), 'candidates.0.content.parts.0.text', '');
            $json = $this->extractJson($text);

            if (! is_array($json) || ! isset($json['recommendations']) || ! is_array($json['recommendations'])) {
                Log::warning('Gemini returned invalid JSON.', [
                    'model' => $model,
                    'text' => $text,
                ]);

                continue;
            }

            $recommendations = array_values(array_filter(
                $json['recommendations'],
                fn ($row) => is_array($row)
            ));

            if ($recommendations === []) {
                continue;
            }

            return [
                'recommendations' => array_slice($recommendations, 0, $limit),
                'message' => null,
                'source' => 'ai',
                'model' => $model,
            ];
        }

        return $this->fallbackRecommendations($candidateBooks, $limit, $lastStatus);
    }

    /**
     * @param  array<int, array{id?: int, title: string, author: string, category: string}>  $candidateBooks
     * @return array<string, mixed>
     */
    private function fallbackRecommendations(array $candidateBooks, int $limit, ?int $status = null, ?string $customMessage = null): array
    {
        $message = $customMessage ?? match ($status) {
            429 => 'AI kotası geçici olarak doldu; kütüphaneden öneriler gösteriliyor. Biraz sonra tekrar dene.',
            404 => 'AI modeli bulunamadı; kütüphaneden öneriler gösteriliyor.',
            default => 'AI şu an yanıt vermedi; kütüphaneden öneriler gösteriliyor.',
        };

        $picked = array_slice($candidateBooks, 0, $limit);
        $score = 92;
        $recommendations = [];

        foreach ($picked as $book) {
            $recommendations[] = [
                'id' => $book['id'] ?? null,
                'title' => $book['title'],
                'author' => $book['author'],
                'genre' => $book['category'],
                'matchScore' => $score,
                'reason' => 'Şu an AI yerine kütüphanedeki aday kitaplardan seçildi. Filtreleri değiştirip tekrar deneyebilirsin.',
            ];
            $score = max(70, $score - 4);
        }

        return [
            'recommendations' => $recommendations,
            'message' => $message,
            'source' => 'fallback',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildUserContext(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $topCategories = Category::query()
            ->select('categories.id', 'categories.name')
            ->join('books', 'categories.id', '=', 'books.category_id')
            ->join('book_user', 'books.id', '=', 'book_user.book_id')
            ->where('book_user.user_id', $user->id)
            ->whereIn('book_user.status', ['okundu', 'okuyorum'])
            ->groupBy('categories.id', 'categories.name')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(5)
            ->get()
            ->map(fn (Category $c) => ['id' => $c->id, 'name' => $c->name])
            ->values()
            ->all();

        $recentReads = $user->books()
            ->wherePivot('status', 'okundu')
            ->with('category')
            ->latest('book_user.updated_at')
            ->take(5)
            ->get(['books.id', 'books.title', 'books.author']);

        return [
            'top_categories' => $topCategories,
            'recent_reads' => $recentReads->map(fn (Book $b) => [
                'id' => $b->id,
                'title' => $b->title,
                'author' => $b->author,
                'category' => $b->category?->name,
            ])->values()->all(),
            'reading_goal' => [
                'year' => now()->year,
                'target' => $user->hasActiveReadingGoal() ? (int) $user->reading_goal : null,
                'current' => $user->booksReadInYear(now()->year),
            ],
        ];
    }

    /**
     * @param  array<int, array{id?: int, title: string, author: string, category: string}>  $candidateBooks
     */
    private function buildCandidateText(array $candidateBooks): string
    {
        $rows = [];
        foreach ($candidateBooks as $book) {
            $id = $book['id'] ?? '?';
            $rows[] = '- id:'.$id.' | '.$book['title'].' | '.$book['author'].' | '.$book['category'];
        }

        return implode("\n", $rows);
    }

    private function callGemini(string $apiKey, string $model, string $systemPrompt, string $userPrompt): ?Response
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/'.$model.':generateContent?key='.urlencode($apiKey);

        try {
            return Http::timeout(25)
                ->connectTimeout(8)
                ->post($url, [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $systemPrompt."\n\n".$userPrompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.5,
                        'maxOutputTokens' => 2048,
                        'responseMimeType' => 'application/json',
                    ],
                ]);
        } catch (\Throwable $e) {
            Log::warning('Gemini call exception.', [
                'model' => $model,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function extractJson(string $text): ?array
    {
        $text = trim($text);
        $text = preg_replace('/^```(?:json)?/i', '', $text) ?? $text;
        $text = preg_replace('/```$/', '', $text) ?? $text;

        $start = strpos($text, '{');
        $end = strrpos($text, '}');

        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        $jsonText = substr($text, $start, $end - $start + 1);
        $decoded = json_decode($jsonText, true);

        return is_array($decoded) ? $decoded : null;
    }
}
