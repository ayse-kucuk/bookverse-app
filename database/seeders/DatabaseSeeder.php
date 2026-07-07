<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Book;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. TEST KULLANICILARI 
        User::factory()->create([
            'name' => 'Ayşe Developer',
            'email' => 'ayse@example.com',
            'password' => Hash::make('password123'),
        ]);

        User::factory()->create([
            'name' => 'Admin İrem',
            'email' => 'irem@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);

        // 2. TEST KATEGORİLERİ
        $fantastik = Category::create(['name' => 'Fantastik']);
        $bilimKurgu = Category::create(['name' => 'Bilim Kurgu']);

        // 3. TEST KİTAPLARI
        Book::create([
    'title' => 'Cam Şato (Throne of Glass)',
    'author' => 'Sarah J. Maas',
    'image_url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR7jFwgb7Gw06OcHs8F8fe-ozqtHXWRIG2rZBbUpvhtsw&s=10',
    'description' => 'Genç bir suikastçı olan Celaena Sardothien\'in özgürlüğü için girdiği ölümcül turnuvayı ve krallığın karanlık sırlarını konu alan epik fantastik seri.',
    'category_id' => $fantastik->id,
    'page_count' => 416,
]);

        Book::create([
            'title' => 'Dune',
            'author' => 'Frank Herbert',
            'image_url' => 'https://img.kitapyurdu.com/v1/getImage/fn:11494736/wi:500/wh:9d3c3c37e',
            'description' => 'Arrakis adındaki çöl gezegeninde, evrenin en değerli maddesi olan "baharat" üzerindeki hakimiyet mücadelesini ve Paul Atreides\'in yükselişini anlatan başyapıt.',
            'category_id' => $bilimKurgu->id,
            'page_count' => 712,
        ]);
    }
}