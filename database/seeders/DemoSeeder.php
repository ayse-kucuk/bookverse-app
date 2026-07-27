<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $demo = User::updateOrCreate(
            ['email' => 'demo@bookverse.app'],
            [
                'name' => 'Demo Okuyucu',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_admin' => false,
                'account_visibility' => User::VISIBILITY_PUBLIC,
                'reading_goal' => 24,
                'reading_goal_year' => now()->year,
            ]
        );

        $ayse = User::where('email', 'ayse@example.com')->first();
        $mehmet = User::where('email', 'mehmet@example.com')->first();

        if ($ayse && $mehmet) {
            $demo->follow($ayse);
            $mehmet->follow($demo);
            $ayse->follow($mehmet);
        }

        $books = Book::query()->inRandomOrder()->limit(8)->get();

        if ($books->isEmpty()) {
            $this->command?->warn('Demo seed atlandi: veritabaninda kitap yok.');

            return;
        }

        $statuses = ['okuyorum', 'okuyacagim', 'okundu'];

        foreach ($books->take(6) as $index => $book) {
            $status = $statuses[$index % 3];
            $demo->books()->syncWithoutDetaching([
                $book->id => [
                    'status' => $status,
                    'rating' => $status === 'okundu' ? 5 : null,
                ],
            ]);
        }

        foreach ($books->take(2) as $book) {
            Comment::updateOrCreate(
                ['user_id' => $demo->id, 'book_id' => $book->id],
                [
                    'rating' => 5,
                    'content' => 'Bookverse demo incelemesi: '.$book->title.' gerçekten okumaya değer. Karakter derinliği ve anlatım çok güçlü.',
                ]
            );
        }

        $demoPosts = [
            [
                'type' => 'thought',
                'content' => 'Bu hafta sonu kahve eşliğinde fantastik bir roman bitirdim. Sizin favori okuma ritüeliniz ne?',
                'book_id' => null,
            ],
            [
                'type' => 'quote',
                'content' => '"Okumak, ruhun gıdasıdır." — Her akşam en az 20 sayfa okumaya çalışıyorum.',
                'book_id' => $books->first()->id,
            ],
            [
                'type' => 'thought',
                'content' => 'Bookverse topluluğuna merhaba! 📚 Birlikte kitap keşfedelim.',
                'book_id' => null,
            ],
        ];

        foreach ($demoPosts as $data) {
            Post::updateOrCreate(
                ['user_id' => $demo->id, 'content' => $data['content']],
                [
                    'type' => $data['type'],
                    'book_id' => $data['book_id'],
                ]
            );
        }

        if ($ayse) {
            Post::updateOrCreate(
                ['user_id' => $ayse->id, 'content' => 'Yeni başladığım bilim kurgu romanından bir alıntı paylaşmak istedim — evrenin sonsuzluğu karşısında insan ne kadar küçük hissediyor...'],
                [
                    'type' => 'thought',
                    'book_id' => $books->skip(1)->first()?->id,
                ]
            );
        }

        if ($mehmet) {
            Post::updateOrCreate(
                ['user_id' => $mehmet->id, 'content' => 'Polisiye türünde bir öneri arıyorum. Agatha Christie severler burada mı?'],
                [
                    'type' => 'thought',
                    'book_id' => null,
                ]
            );
        }

        $demoPost = Post::where('user_id', $demo->id)->first();

        if ($demoPost && $ayse) {
            PostLike::firstOrCreate([
                'post_id' => $demoPost->id,
                'user_id' => $ayse->id,
            ]);

            PostComment::updateOrCreate(
                ['post_id' => $demoPost->id, 'user_id' => $ayse->id],
                ['content' => 'Harika paylaşım! Ben de her akşam okuyorum.']
            );
        }

        if ($demoPost && $mehmet) {
            PostLike::firstOrCreate([
                'post_id' => $demoPost->id,
                'user_id' => $mehmet->id,
            ]);
        }

        $this->command?->info('Demo verisi hazir: demo@bookverse.app / password');
    }
}
