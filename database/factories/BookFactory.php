<?php

namespace Database\Factories;

use App\Models\Book;
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
        'title' => fake()->sentence(3), // Rastgele 3 kelimelik bir kitap adı üretir
        'author' => fake()->name(),     // Rastgele bir yazar adı üretir
        'description' => fake()->paragraph(2), // Rastgele 2 paragraflık bir kitap özeti üretir
        'page_count' => fake()->numberBetween(100, 800), // 100 ile 800 arasında rastgele sayfa sayısı
        'cover_image' => 'https://picsum.photos/200/300', // Test için rastgele bir resim linki
    ];
}
}
