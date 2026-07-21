<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingGoalTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_set_reading_goal(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('reading-goal.update'), ['reading_goal' => 12])
            ->assertRedirect()
            ->assertSessionHas('success');

        $user->refresh();

        $this->assertSame(12, $user->reading_goal);
        $this->assertSame(now()->year, $user->reading_goal_year);
        $this->assertTrue($user->hasActiveReadingGoal());
    }

    public function test_user_can_remove_reading_goal(): void
    {
        $user = User::factory()->create([
            'reading_goal' => 20,
            'reading_goal_year' => now()->year,
        ]);

        $this->actingAs($user)
            ->delete(route('reading-goal.destroy'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $user->refresh();

        $this->assertNull($user->reading_goal);
        $this->assertNull($user->reading_goal_year);
        $this->assertFalse($user->hasActiveReadingGoal());
    }

    public function test_books_read_in_year_counts_only_completed_books(): void
    {
        $user = User::factory()->create();
        $readBook = Book::factory()->create();
        $readingBook = Book::factory()->create();
        $oldReadBook = Book::factory()->create();

        $user->books()->attach($readBook->id, ['status' => 'okundu']);
        $user->books()->attach($readingBook->id, ['status' => 'okuyorum']);
        $user->books()->attach($oldReadBook->id, ['status' => 'okundu']);

        \Illuminate\Support\Facades\DB::table('book_user')
            ->where('user_id', $user->id)
            ->where('book_id', $oldReadBook->id)
            ->update(['updated_at' => now()->subYear()]);

        $this->assertSame(1, $user->booksReadInYear());
    }

    public function test_reading_goal_stats_calculate_progress(): void
    {
        $user = User::factory()->create([
            'reading_goal' => 4,
            'reading_goal_year' => now()->year,
        ]);

        $books = Book::factory()->count(2)->create();
        foreach ($books as $book) {
            $user->books()->attach($book->id, ['status' => 'okundu']);
        }

        $stats = $user->readingGoalStats();

        $this->assertSame(4, $stats['target']);
        $this->assertSame(2, $stats['current']);
        $this->assertSame(50, $stats['percentage']);
        $this->assertSame(2, $stats['remaining']);
        $this->assertFalse($stats['completed']);
    }

    public function test_profile_page_shows_reading_goal_section(): void
    {
        $user = User::factory()->create([
            'reading_goal' => 10,
            'reading_goal_year' => now()->year,
        ]);

        $this->actingAs($user)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('Okuma Hedefi')
            ->assertSee('10');
    }

    public function test_api_me_includes_reading_goal_stats(): void
    {
        $user = User::factory()->create([
            'reading_goal' => 8,
            'reading_goal_year' => now()->year,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('user.reading_goal.target', 8)
            ->assertJsonPath('user.reading_goal.year', now()->year);
    }

    public function test_guest_cannot_update_reading_goal(): void
    {
        $this->post(route('reading-goal.update'), ['reading_goal' => 5])
            ->assertRedirect(route('login'));
    }
}
