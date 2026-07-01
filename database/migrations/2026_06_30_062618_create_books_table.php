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
        Schema::create('books', function (Blueprint $table) {
        $table->id();                               
        $table->string('title');
        // categories tablosundaki id ile bağ kurar; o kategori silinirse ona ait tüm kitapları da otomatik siler.
        $table->foreignId('category_id')->constrained()->onDelete('cascade');                    
        $table->string('author');                   
        $table->text('description')->nullable();     
        $table->integer('page_count');              
        $table->string('cover_image')->nullable();  
        $table->timestamps();                       
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
