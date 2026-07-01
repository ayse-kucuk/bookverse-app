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
        Schema::create('book_users', function (Blueprint $table) {
        $table->id();
         // Kimin kütüphanesi?
        $table->foreignId('user_id')->constrained()->onDelete('cascade');       
        // Hangi kitap?
        $table->foreignId('book_id')->constrained()->onDelete('cascade');       
        // Okuma Durumu: 'reading' (Okuyor), 'completed' (Okudu), 'plan_to_read' (Okuyacak)
        // Varsayılan olarak 'plan_to_read'
        $table->string('status')->default('plan_to_read');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_users');
    }
};
