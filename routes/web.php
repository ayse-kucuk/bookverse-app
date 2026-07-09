<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;
use App\Models\User;

// Ana sayfa: sosyal akış
Route::get('/', [FeedController::class, 'index'])->name('home');

// Kitap keşfet sayfası
Route::get('/kesfet', [BookController::class, 'index'])->name('explore');

Route::get('/books/{id}', [BookController::class, 'show'])->name('books.show');
Route::post('/books/{id}/comment', [BookController::class, 'storeComment'])->middleware('auth')->name('books.comment.store');
Route::post('/books/{id}/status', [BookController::class, 'updateStatus'])->middleware('auth')->name('books.status.update');

Route::get('/dashboard', function () {
    return redirect()->route('home');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';

Route::middleware('auth')->group(function () {
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    Route::post('/users/{user}/follow', [FollowController::class, 'store'])->name('users.follow');
    Route::delete('/users/{user}/follow', [FollowController::class, 'destroy'])->name('users.unfollow');

    Route::get('/profile', [BookController::class, 'profile'])->name('profile');
    Route::get('/account-settings', [ProfileController::class, 'edit'])->name('account.settings');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/users/{user}', [UserProfileController::class, 'show'])->name('users.show');

Route::get('/test-profile', function() {
    $user = User::where('email', 'ayse@example.com')->first();
    
    if (!$user) {
        return response()->json(['error' => 'Kullanıcı bulunamadı, seeder çalıştırmalısınız.'], 404);
    }

    $userBooks = $user->books;

    return response()->json([
        'user' => $user->name,
        'reading' => $userBooks->where('pivot.status', 'okuyorum')->values(),
        'willRead' => $userBooks->where('pivot.status', 'okuyacagim')->values(),
        'read' => $userBooks->where('pivot.status', 'okundu')->values(),
    ]);
});

Route::middleware(['auth', 'App\Http\Middleware\AdminMiddleware'])->group(function () {
    Route::get('/admin', function () {
        return "Hoş geldin Admin İrem! Burası backend korumalı yönetim paneli. 🛠️";
    })->name('admin.dashboard');

    Route::get('/admin/books/create', [BookController::class, 'createBook'])->name('admin.books.create');
    Route::post('/admin/books', [BookController::class, 'storeBook'])->name('admin.books.store');
    Route::get('/admin/books/{id}/edit', [BookController::class, 'editBook'])->name('admin.books.edit');
    Route::put('/admin/books/{id}', [BookController::class, 'updateBook'])->name('admin.books.update');
});
