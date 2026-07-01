<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    // İlişki Tanımı: Bir kategorinin birden fazla kitabı olabilir
    public function books()
    {
        return $this->hasMany(Book::class);
    }

    // Toplu veri yükleme izni olan sütunlar
    protected $fillable = [
        'name',
        'description'
    ];
}