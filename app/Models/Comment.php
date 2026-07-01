<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    // Toplu veri yükleme izni olan sütunlar
    protected $fillable = [
        'content',
        'book_id',
        'user_id',
        'rating'
    ];

    // Bir yorum sadece bir kitaba aittir
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    // Bir yorum sadece bir kullanıcıya aittir
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Bir yorumun birden fazla beğenisi (Like satırı) olabilir
    public function likes()
    {
        return $this->hasMany(CommentLike::class);
    }
}