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
     * @param  array<int, array{title: string, author: string, category: string}>  $candidateBooks
     * @return array<string, mixed>
     */
    public function recommend(?User $user, array $params, array $candidateBooks): array
    {
        $apiKey = (string) config('services.gemini.key', '');

        if ($apiKey === '') {
            return [
                'recommendations' => [],
                'message' => 'GEMINI_API_KEY eksik.',
            ];
        }

        $model = (string) config('services.gemini.model', 'gemini-flash-latest');

        $systemPrompt = implode("\n", [
            'You are an expert literature critic and a personalized book recommendation assistant for a Turkish reading community (Bookverse).',
            'You must respond with ONLY valid JSON. No markdown, no code fences, no extra keys.',
            'Return exactly this schema:',
            '{',
            '  "recommendations": [',
            '    {',
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
            '- Use the exact candidate title, author, and genre values.',
            '- matchScore must be an integer from 0 to 100.',
            '- reason must be in Turkish.',
            '- If no recommendation fits, return an empty array for "recommendations".',
        ]);

        $mood = (string) ($params['mood'] ?? '');
        $genreName = (string) ($params['genre_name'] ?? '');
        $freeText = (string) ($params['free_text'] ?? '');
        $limit = (int) ($params['limit'] ?? 5);

        $limit = max(1, min($limit, 10));

        $userContext = $this->buildUserContext($user);

        $candidateText = $this->buildCandidateText($candidateBooks);

        $userPrompt = implode("\n", [
            'User request:',
            json_encode([
                'mood' => $mood ?: null,
                'genre' => $genreName ?: null,
                'free_text' => $freeText ?: null,
                'limit' => $limit,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            '',
            'User context from database (may be empty):',
            json_encode($userContext, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            '',
            'Candidate books (you MUST pick ONLY from these):',
            $candidateText,
        ]);

        $response = $this->callGemini($apiKey, $model, $systemPrompt, $userPrompt);

        if (! $response instanceof Response || ! $response->successful()) {
            Log::warning('Gemini recommend call failed.', [
                'status' => $response?->status(),
                'body' => $response?->body(),
            ]);

            return $this->fallbackRecommendations($candidateBooks, $limit, $response?->status());
        }

        $text = (string) data_get($response->json(), 'candidates.0.content.parts.0.text', '');
        $json = $this->extractJson($text);

        if (! is_array($json) || ! isset($json['recommendations']) || ! is_array($json['recommendations'])) {
            Log::warning('Gemini returned invalid JSON.', ['text' => $text]);

            return $this->fallbackRecommendations($candidateBooks, $limit, null, 'AI yanıtı bozuldu; kütüphaneden öneriler gösteriliyor.');
        }

        $recommendations = $json['recommendations'];

        if ($recommendations === []) {
            return $this->fallbackRecommendations($candidateBooks, $limit, null, 'AI uygun eşleşme bulamadı; kütüphaneden öneriler gösteriliyor.');
        }

        return [
            'recommendations' => $recommendations,
            'message' => null,
            'source' => 'ai',
        ];
    }

    /**
     * Gemini kota/hata durumunda aday listesinden basit öneri üretir.
     *
     * @param  array<int, array{title: string, author: string, category: string}>  $candidateBooks
     * @return array<string, mixed>
     */
    private function fallbackRecommendations(array $candidateBooks, int $limit, ?int $status = null, ?string $customMessage = null): array
    {
        $message = $customMessage;

        if ($message === null) {
            $message = match ($status) {
                429 => 'AI kotası geçici olarak doldu; kütüphaneden öneriler gösteriliyor. ~1 dk sonra tekrar deneyebilirsin.',
                404 => 'AI modeli bulunamadı; kütüphaneden öneriler gösteriliyor.',
                default => 'AI şu an yanıt vermedi; kütüphaneden öneriler gösteriliyor.',
            };
        }

        $picked = array_slice($candidateBooks, 0, $limit);
        $score = 92;

        $recommendations = [];
        foreach ($picked as $book) {
            $recommendations[] = [
                'title' => $book['title'],
                'author' => $book['author'],
                'genre' => $book['category'],
                'matchScore' => $score,
                'reason' => 'Şu an AI yerine kütüphanedeki aday kitaplardan seçildi. Beğenmezsen filtreleri değiştirip tekrar dene.',
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

        // Prefer edilen türler: okundu + okuyorum.
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
     * @param  array<int, array{title: string, author: string, category: string}>  $candidateBooks
     */
    private function buildCandidateText(array $candidateBooks): string
    {
        $rows = [];
        foreach ($candidateBooks as $book) {
            $rows[] = '- '.$book['title'].' | '.$book['author'].' | '.$book['category'];
        }

        return implode("\n", $rows);
    }

    private function callGemini(string $apiKey, string $model, string $systemPrompt, string $userPrompt): ?Response
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/'.$model.':generateContent?key='.urlencode($apiKey);

        try {
            return Http::timeout(20)
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
                        'temperature' => 0.6,
                        'maxOutputTokens' => 1024,
                        'responseMimeType' => 'application/json',
                    ],
                ]);
        } catch (\Throwable $e) {
            Log::warning('Gemini call exception.', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function extractJson(string $text): ?array
    {
        $text = trim($text);

        // Common cases: ```json { ... } ```
        $text = preg_replace('/^```(?:json)?/i', '', $text);
        $text = preg_replace('/```$/', '', $text);

        // If the model still wrapped extra text, try to locate first JSON object.
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

