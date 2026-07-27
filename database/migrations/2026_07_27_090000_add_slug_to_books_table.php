<?php

use App\Models\Book;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('title');
        });

        Book::query()->orderBy('id')->each(function (Book $book) {
            $book->forceFill([
                'slug' => Book::generateUniqueSlug($book->title, $book->author, $book->id),
            ])->saveQuietly();
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
