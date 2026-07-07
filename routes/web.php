<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;
use App\Models\User; // Tüm use ifadelerini en tepeye topladık

// 1. ANASAYFA: Kitap listesi
Route::get('/', [BookController::class, 'index'])->name('home');

// 2. DETAY SAYFASI: Kitap detaylarını gösterecek olan rota
Route::get('/books/{id}', [BookController::class, 'show'])->name('books.show');

// 3. YORUM GÖNDERME: Formun post edileceği rota
Route::post('/books/{id}/comment', [BookController::class, 'storeComment'])->middleware('auth')->name('books.comment.store');

// 4. KÜTÜPHANE DURUMU GÜNCELLEME
Route::post('/books/{id}/status', [BookController::class, 'updateStatus'])->middleware('auth')->name('books.status.update');

// --- Breeze'in Kendi Dashboard Rotaları ---
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// NOT: Breeze'in varsayılan profil rotalarını bizim Goodreads profilimizle çakışmasın diye kaldırdık/Yorum satırı yaptık.

require __DIR__.'/auth.php';

// Bizim Goodreads Tarzı Profil Sayfamız
Route::get('/profile', [BookController::class, 'profile'])->middleware('auth')->name('profile');

// --- Postman Test Rotası ---
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
// Sadece giriş yapmış olan ve ADMIN olan kullanıcılar erişebilir
Route::middleware(['auth', 'App\Http\Middleware\AdminMiddleware'])->group(function () {
    
    // Örnek Admin Paneli Anasayfası
    Route::get('/admin', function () {
        return "Hoş geldin Admin İrem! Burası backend korumalı yönetim paneli. 🛠️";
    })->name('admin.dashboard');

    // 1. Kitap Ekleme Sayfası (Formu Gösterecek)
    Route::get('/admin/books/create', [BookController::class, 'createBook'])->name('admin.books.create');

    // 2. Kitabı Veri Tabanına Kaydedecek Post Rotası
    Route::post('/admin/books', [BookController::class, 'storeBook'])->name('admin.books.store');
    // 3. Kitap Düzenleme Sayfası (Formu Gösterecek - Kitap ID'si ile çalışır)
    Route::get('/admin/books/{id}/edit', [BookController::class, 'editBook'])->name('admin.books.edit');

    // 4. Kitabı Güncelleyecek Rota (Veri tabanındaki satırı update eder)
    Route::put('/admin/books/{id}', [BookController::class, 'updateBook'])->name('admin.books.update');
});

