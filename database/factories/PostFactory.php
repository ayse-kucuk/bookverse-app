<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'book_id' => null,
            'type' => 'thought',
            'content' => fake()->paragraph(),
        ];
    }

    public function quote(): static
    {
        return $this->state(fn () => [
            'type' => 'quote',
            'book_id' => Book::factory(),
            'content' => '"' . fake()->sentence() . '"',
        ]);
    }
}
