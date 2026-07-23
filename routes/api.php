<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PostCommentController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\TwoFactorController as ApiTwoFactorController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/two-factor', [AuthController::class, 'verifyTwoFactor']);

Route::get('/books', [BookController::class, 'index']);
Route::get('/books/{id}', [BookController::class, 'show']);

Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{post}', [PostController::class, 'show']);
Route::get('/users/{user}', [UserController::class, 'show']);
Route::get('/search', [SearchController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/two-factor', [ApiTwoFactorController::class, 'show']);
    Route::post('/two-factor/setup', [ApiTwoFactorController::class, 'setup']);
    Route::post('/two-factor/confirm', [ApiTwoFactorController::class, 'confirm']);
    Route::post('/two-factor/disable', [ApiTwoFactorController::class, 'disable']);

    Route::post('/posts', [PostController::class, 'store']);
    Route::post('/posts/{post}/like', [PostController::class, 'toggleLike']);
    Route::post('/posts/{post}/comments', [PostCommentController::class, 'store']);
    Route::delete('/posts/{post}/comments/{comment}', [PostCommentController::class, 'destroy']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);

    Route::post('/users/{user}/follow', [UserController::class, 'follow']);
    Route::delete('/users/{user}/follow', [UserController::class, 'unfollow']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read']);
});
