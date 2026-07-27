<?php

namespace Tests\Unit;

use App\Services\AiBookRequestValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AiBookRequestValidatorTest extends TestCase
{
    private AiBookRequestValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new AiBookRequestValidator;
    }

    #[DataProvider('validRequestsProvider')]
    public function test_accepts_book_related_requests(?string $mood, ?string $genre, ?string $freeText): void
    {
        $result = $this->validator->validate($mood, $genre, $freeText);

        $this->assertTrue($result['valid']);
        $this->assertNull($result['message']);
    }

    public static function validRequestsProvider(): array
    {
        return [
            'empty free text' => [null, null, null],
            'sci-fi request' => [null, null, 'Uzayda geçen aksiyon dolu bilimkurgu arıyorum'],
            'mood only context' => ['Sürükleyici', null, 'kısa bir şey'],
            'genre context' => [null, 'Fantastik', 'epik olsun'],
            'english request' => [null, null, 'I want a dark fantasy novel with strong characters'],
        ];
    }

    #[DataProvider('invalidRequestsProvider')]
    public function test_rejects_off_topic_requests(?string $mood, ?string $genre, string $freeText): void
    {
        $result = $this->validator->validate($mood, $genre, $freeText);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['message']);
    }

    public static function invalidRequestsProvider(): array
    {
        return [
            'greeting' => [null, null, 'merhaba nasılsın'],
            'food' => [null, null, 'pizza tarifi ver'],
            'football' => [null, null, 'futbol maç skoru nedir'],
            'homework' => [null, null, 'matematik ödevimi yap'],
            'crypto' => [null, null, 'bitcoin ne olur'],
            'movie' => [null, null, 'netflix dizi öner'],
            'random topic' => [null, null, 'bugün hava çok güzel'],
        ];
    }
}
