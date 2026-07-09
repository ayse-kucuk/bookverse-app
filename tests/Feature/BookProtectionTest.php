<?php

namespace Tests\Feature;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_protected_books_cannot_be_deleted(): void
    {
        $book = Book::factory()->create([
            'is_protected' => true,
        ]);

        $deleted = $book->delete();

        $this->assertFalse($deleted);
        $this->assertNotNull($book->fresh());
    }
}
