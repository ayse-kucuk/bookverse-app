<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookUser extends Model
{
    // Laravel'in tablo ismini otomatik bulması için
    protected $table = 'book_user';

    protected static function booted(): void
    {
        static::creating(function (self $entry): void {
            $entry->is_protected = true;
        });
    }

    protected $fillable = [
        'user_id',
        'book_id',
        'status',
        'is_protected'
    ];
}