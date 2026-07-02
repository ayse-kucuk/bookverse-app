<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

// 1. ANASAYFA: Kitap listesi
Route::get('/', [BookController::class, 'index'])->name('home');

// 2. DETAY SAYFASI: Kitap detaylarını gösterecek olan rota
Route::get('/books/{id}', [BookController::class, 'show'])->name('books.show');

// 3. YORUM GÖNDERME: Formun post edileceği rota (İsim eşleşmesi tam olarak burada yapıldı)
Route::post('/books/{id}/comment', [BookController::class, 'storeComment'])->middleware('auth')->name('books.comment.store');


// --- Breeze'in Kendi Dashboard ve Profil Rotaları ---
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';