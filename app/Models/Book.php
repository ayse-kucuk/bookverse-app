<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    // 2. Adım: Sınıfın içine bu özelliği dahil ediyoruz (En kritik yer burası)
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'description',
        'page_count',
        'cover_image'
    ];
}