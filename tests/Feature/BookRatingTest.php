<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookRatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_rate_a_book_via_json(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user)
            ->postJson(route('books.rating.update', $book), ['rating' => 4])
            ->assertOk()
            ->assertJson([
                'user_rating' => 4,
                'average_rating' => 4.0,
                'ratings_count' => 1,
            ]);
    }

    public function test_authenticated_user_can_rate_a_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('books.rating.update', $book), [
                'rating' => 4,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('book_user', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
        ]);
    }

    public function test_user_can_update_their_rating(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $user->books()->attach($book->id, [
            'status' => 'okundu',
            'rating' => 3,
            'is_protected' => true,
        ]);

        $this->actingAs($user)
            ->post(route('books.rating.update', $book), ['rating' => 5])
            ->assertRedirect();

        $this->assertDatabaseHas('book_user', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'status' => 'okundu',
        ]);
    }

    public function test_guest_cannot_rate_a_book(): void
    {
        $book = Book::factory()->create();

        $this->post(route('books.rating.update', $book), ['rating' => 5])
            ->assertRedirect(route('login'));
    }

    public function test_book_detail_shows_average_rating(): void
    {
        $book = Book::factory()->create();
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $userA->books()->attach($book->id, ['status' => 'okundu', 'rating' => 4]);
        $userB->books()->attach($book->id, ['status' => 'okundu', 'rating' => 5]);

        $this->get(route('books.show', $book))
            ->assertOk()
            ->assertSee('4.5')
            ->assertSee('2 puan');
    }

    public function test_invalid_rating_is_rejected(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user)
            ->post(route('books.rating.update', $book), ['rating' => 6])
            ->assertSessionHasErrors('rating');
    }
}
