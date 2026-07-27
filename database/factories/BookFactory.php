<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
{
    return [
        'category_id' => Category::firstOrCreate(['name' => 'Test Kategori'])->id,
        'title' => fake()->sentence(3),
        'author' => fake()->name(),
        'description' => fake()->paragraph(2),
        'page_count' => fake()->numberBetween(100, 800),
        'image_url' => 'https://picsum.photos/200/300',
        'is_protected' => false,
    ];
}
}
