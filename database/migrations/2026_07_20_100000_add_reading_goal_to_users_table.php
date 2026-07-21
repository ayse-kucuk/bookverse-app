<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedSmallInteger('reading_goal')->nullable()->after('account_visibility');
            $table->unsignedSmallInteger('reading_goal_year')->nullable()->after('reading_goal');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['reading_goal', 'reading_goal_year']);
        });
    }
};
