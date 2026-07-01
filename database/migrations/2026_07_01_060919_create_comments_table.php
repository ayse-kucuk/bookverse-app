<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->text('content'); // Kullanıcının yazdığı yorum metni
           $table->foreignId('book_id')->constrained()->onDelete('cascade');
 // 2. Bu yorumu hangi kullanıcının (üye) yazdığını tutar. Kullanıcı silinirse yorumu da silinir.
           $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('rating')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
