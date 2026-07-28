<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_translations', function (Blueprint $table) {
            $table->id();
            $table->string('source_hash', 64);
            $table->string('source_locale', 5);
            $table->string('target_locale', 5);
            $table->text('source_text');
            $table->text('translated_text');
            $table->string('context', 32)->default('general');
            $table->timestamps();

            $table->unique(['source_hash', 'source_locale', 'target_locale']);
            $table->index(['target_locale', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_translations');
    }
};
