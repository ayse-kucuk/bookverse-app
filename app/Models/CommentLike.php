<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentLike extends Model
{
    // Toplu veri yükleme izni olan sütunlar
    protected $fillable = [
        'user_id',
        'comment_id'
    ];

    // İlişki: Bu beğeni satırı bir kullanıcıya aittir
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // İlişki: Bu beğeni satırı bir yoruma aittir
    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}