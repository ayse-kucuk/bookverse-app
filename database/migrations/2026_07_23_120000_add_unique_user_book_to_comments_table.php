<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kullanıcı başına bir inceleme kalsın: eski çiftleri temizle
        $duplicates = DB::table('comments')
            ->select('user_id', 'book_id', DB::raw('MIN(id) as keep_id'))
            ->groupBy('user_id', 'book_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $row) {
            DB::table('comments')
                ->where('user_id', $row->user_id)
                ->where('book_id', $row->book_id)
                ->where('id', '!=', $row->keep_id)
                ->delete();
        }

        Schema::table('comments', function (Blueprint $table) {
            $table->unique(['user_id', 'book_id'], 'comments_user_book_unique');
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropUnique('comments_user_book_unique');
        });
    }
};
