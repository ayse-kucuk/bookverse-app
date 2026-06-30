<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BookController; 

Route::get('/', function () {
    return view('welcome');
});


Route::get('/kitaplar', [BookController::class, 'index']);
// Süslü parantez içindeki {id}, oraya gelecek olan herhangi bir sayıyı temsil eder
Route::get('/kitaplar/{id}', [BookController::class, 'show']);