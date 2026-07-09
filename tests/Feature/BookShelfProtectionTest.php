<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookShelfProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_shelf_entries_are_protected_automatically(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post("/books/{$book->id}/status", [
                'status' => 'okuyorum',
            ]);

        $response->assertSessionHasNoErrors();

        $pivot = $user->books()->where('books.id', $book->id)->first()->pivot;

        $this->assertTrue((bool) $pivot->is_protected);
        $this->assertSame('okuyorum', $pivot->status);
    }
}
