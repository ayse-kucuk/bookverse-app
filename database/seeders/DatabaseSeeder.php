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
        // 1. Kullanıcıları oluştur
        $users = [
            ['email' => 'ayseekucuk33@gmail.com', 'name' => 'Ayşe Kucuk', 'is_admin' => true],
            ['email' => 'ayse@example.com', 'name' => 'Ayşe Developer', 'is_admin' => false],
            ['email' => 'irem@example.com', 'name' => 'Admin İrem', 'is_admin' => true],
            ['email' => 'mehmet@example.com', 'name' => 'Mehmet Kullanıcı', 'is_admin' => false],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password123'), // Hepsine ortak şifre
                    'email_verified_at' => now(),
                    'is_admin' => $userData['is_admin'],
                    'account_visibility' => User::VISIBILITY_PUBLIC,
                ]
            );
        }

        // 2. Kategorileri genişlet
        $kategoriIsimleri = [
            'Fantastik', 
            'Bilim Kurgu', 
            'Klasikler', 
            'Kişisel Gelişim', 
            'Polisiye', 
            'Tarih', 
            'Psikoloji', 
            'Biyografi'
        ];

        foreach ($kategoriIsimleri as $isim) {
            Category::firstOrCreate(['name' => $isim]);
        }

        // Kategori ID'lerini çek
        $fantastik = Category::where('name', 'Fantastik')->first();
        $bilimKurgu = Category::where('name', 'Bilim Kurgu')->first();

        // 3. Kitapları oluştur
        Book::updateOrCreate(
            ['title' => 'Cam Şato (Throne of Glass)', 'author' => 'Sarah J. Maas'],
            [
                'image_url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR7jFwgb7Gw06OcHs8F8fe-ozqtHXWRIG2rZBbUpvhtsw&s=10',
                'description' => 'Genç bir suikastçı olan Celaena Sardothien\'in özgürlüğü için girdiği ölümcül turnuvayı ve krallığın karanlık sırlarını konu alan epik fantastik seri.',
                'category_id' => $fantastik->id,
                'page_count' => 416,
                'is_protected' => true,
            ]
        );

        Book::updateOrCreate(
            ['title' => 'Dune', 'author' => 'Frank Herbert'],
            [
                'image_url' => 'https://img.kitapyurdu.com/v1/getImage/fn:11494736/wi:500/wh:9d3c3c37e',
                'description' => 'Arrakis adındaki çöl gezegeninde, evrenin en değerli maddesi olan "baharat" üzerindeki hakimiyet mücadelesini ve Paul Atreides\'in yükselişini anlatan başyapıt.',
                'category_id' => $bilimKurgu->id,
                'page_count' => 712,
                'is_protected' => true,
            ]
        );

        // 4. Google Books API'den otomatik kitap ekleme
        $this->call(BookSeeder::class);
    }
}