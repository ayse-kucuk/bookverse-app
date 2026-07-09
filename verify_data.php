<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Book;

$users = User::whereIn('email', ['ayseekucuk33@gmail.com', 'ayse@example.com', 'irem@example.com', 'mehmet@example.com'])->get();
foreach ($users as $user) {
    echo $user->email . ' | ' . $user->name . ' | admin=' . ($user->is_admin ? 'yes' : 'no') . PHP_EOL;
}
echo "---" . PHP_EOL;
foreach (Book::all() as $book) {
    echo $book->title . PHP_EOL;
}
