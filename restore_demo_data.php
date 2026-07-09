<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Category;
use App\Models\Book;
use Illuminate\Support\Facades\Hash;

$admin = User::updateOrCreate(
    ['email' => 'ayseekucuk33@gmail.com'],
    ['name' => 'Ayşe Kucuk', 'password' => Hash::make('12345678'), 'email_verified_at' => now()]
);
$admin->forceFill(['is_admin' => true])->save();

User::updateOrCreate(
    ['email' => 'ayse@example.com'],
    ['name' => 'Ayşe Developer', 'password' => Hash::make('password123')]
);

User::updateOrCreate(
    ['email' => 'irem@example.com'],
    ['name' => 'Admin İrem', 'password' => Hash::make('password123'), 'is_admin' => true]
);

User::updateOrCreate(
    ['email' => 'mehmet@example.com'],
    ['name' => 'Mehmet Kullanıcı', 'password' => Hash::make('password123')]
);

$fantastik = Category::firstOrCreate(['name' => 'Fantastik']);
$bilimKurgu = Category::firstOrCreate(['name' => 'Bilim Kurgu']);

Book::updateOrCreate(
    ['title' => 'Cam Şato (Throne of Glass)', 'author' => 'Sarah J. Maas'],
    [
        'image_url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR7jFwgb7Gw06OcHs8F8fe-ozqtHXWRIG2rZBbUpvhtsw&s=10',
        'description' => 'Genç bir suikastçı olan Celaena Sardothien\'in özgürlüğü için girdiği ölümcül turnuvayı ve krallığın karanlık sırlarını konu alan epik fantastik seri.',
        'category_id' => $fantastik->id,
        'page_count' => 416,
    ]
);

Book::updateOrCreate(
    ['title' => 'Dune', 'author' => 'Frank Herbert'],
    [
        'image_url' => 'https://img.kitapyurdu.com/v1/getImage/fn:11494736/wi:500/wh:9d3c3c37e',
        'description' => 'Arrakis adındaki çöl gezegeninde, evrenin en değerli maddesi olan "baharat" üzerindeki hakimiyet mücadelesini ve Paul Atreides\'in yükselişini anlatan başyapıt.',
        'category_id' => $bilimKurgu->id,
        'page_count' => 712,
    ]
);

echo "restored";
