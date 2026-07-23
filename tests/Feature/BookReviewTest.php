<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_publish_a_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user)
            ->post(route('books.comment.store', $book), [
                'rating' => 5,
                'content' => 'Çok sürükleyici bir roman, karakterler çok iyi işlenmiş.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'content' => 'Çok sürükleyici bir roman, karakterler çok iyi işlenmiş.',
        ]);

        $this->assertDatabaseHas('book_user', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
        ]);
    }

    public function test_user_can_update_their_existing_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        Comment::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'content' => 'İlk izlenimim orta seviyede kaldı biraz.',
        ]);

        $this->actingAs($user)
            ->post(route('books.comment.store', $book), [
                'rating' => 4,
                'content' => 'Tekrar okuyunca daha çok beğendim, tavsiye ederim.',
            ])
            ->assertRedirect();

        $this->assertEquals(1, Comment::where('user_id', $user->id)->where('book_id', $book->id)->count());
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'content' => 'Tekrar okuyunca daha çok beğendim, tavsiye ederim.',
        ]);
    }

    public function test_review_requires_rating_and_long_enough_content(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user)
            ->post(route('books.comment.store', $book), [
                'rating' => null,
                'content' => 'kısa',
            ])
            ->assertSessionHasErrors(['rating', 'content']);
    }

    public function test_user_can_delete_own_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $comment = Comment::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'content' => 'Silinecek örnek inceleme metni burada.',
        ]);

        $this->actingAs($user)
            ->delete(route('books.comment.destroy', [$book, $comment]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_guest_cannot_publish_review(): void
    {
        $book = Book::factory()->create();

        $this->post(route('books.comment.store', $book), [
            'rating' => 5,
            'content' => 'Misafir kullanıcı inceleme yazamamalı burada.',
        ])->assertRedirect(route('login'));
    }

    public function test_book_detail_shows_review_stars(): void
    {
        $user = User::factory()->create(['name' => 'Ayşe Reviewer']);
        $book = Book::factory()->create();

        Comment::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'content' => 'Harika bir kitap, herkese tavsiye ederim gerçekten.',
        ]);

        $this->get(route('books.show', $book))
            ->assertOk()
            ->assertSee('Kitap İncelemeleri')
            ->assertSee('Ayşe Reviewer')
            ->assertSee('Harika bir kitap, herkese tavsiye ederim gerçekten.');
    }
}
