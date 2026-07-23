<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiRecommendationTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_recommend_returns_recommendations_json_structure(): void
    {
        config()->set('services.gemini.key', 'test-key');
        config()->set('services.gemini.model', 'gemini-1.5-flash');

        $categoryA = Category::create(['name' => 'Fantastik']);
        $categoryB = Category::create(['name' => 'Bilim Kurgu']);

        $user = User::factory()->create();

        $readBook = Book::factory()->create([
            'title' => 'Read Book',
            'author' => 'Some Author',
            'category_id' => $categoryA->id,
            'is_protected' => true,
        ]);

        $user->books()->attach($readBook->id, ['status' => 'okundu', 'rating' => null, 'is_protected' => true]);

        $candidate1 = Book::factory()->create([
            'title' => 'Dune',
            'author' => 'Frank Herbert',
            'category_id' => $categoryA->id,
            'is_protected' => true,
            'image_url' => 'https://example.com/dune.jpg',
        ]);

        $candidate2 = Book::factory()->create([
            'title' => 'Foundation',
            'author' => 'Isaac Asimov',
            'category_id' => $categoryA->id,
            'is_protected' => true,
            'image_url' => 'https://example.com/foundation.jpg',
        ]);

        // Not candidate (different category)
        Book::factory()->create([
            'title' => 'Other Category Book',
            'author' => 'Other Author',
            'category_id' => $categoryB->id,
            'is_protected' => true,
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'recommendations' => [
                                            [
                                                'title' => $candidate1->title,
                                                'author' => $candidate1->author,
                                                'genre' => $categoryA->name,
                                                'matchScore' => 95,
                                                'reason' => 'Zengin dünya kurgu ve gerilim dozu yüksek olduğu için sevebilirsin.',
                                            ],
                                            [
                                                'title' => $candidate2->title,
                                                'author' => $candidate2->author,
                                                'genre' => $categoryA->name,
                                                'matchScore' => 88,
                                                'reason' => 'Strateji ve karakter gelişimi ön planda; okuma zevkine uyuyor.',
                                            ],
                                        ],
                                    ], JSON_UNESCAPED_UNICODE),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->postJson(route('ai.recommend'), [
            'mood' => 'Sürükleyici',
            'genre_id' => null,
            'free_text' => 'Uzayda geçen aksiyon dolu bir şeyler istiyorum.',
            'limit' => 5,
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'recommendations' => [
                '*' => [
                    'title',
                    'author',
                    'genre',
                    'matchScore',
                    'reason',
                    'book_id',
                    'image_url',
                    'book_url',
                ],
            ],
        ]);

        $this->assertCount(2, $response->json('recommendations'));
    }
}

