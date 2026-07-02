<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    // İlişki Tanımı: Bir kitap sadece bir kategoriye ait olabilir
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    // İlişki: Bir kitap birden fazla kullanıcının kütüphanesinde yer alabilir (Many-to-Many)
    // Bir kitabın birden çok yorumu olabilir ilişkisi
   public function comments()
  {
    return $this->hasMany(Comment::class);
  }
  public function users()
  {
    return $this->belongsToMany(User::class, 'book_user')
                ->withPivot('status')
                ->withTimestamps();
   }

    // Toplu veri yükleme izni olan sütunlar (category_id'yi buraya ekledik)
    protected $fillable = [
        'category_id', // <-- Yeni eklediğimiz yabancı anahtar sütunu
        'title',
        'author',
        'description',
        'page_count',
        'cover_image'
    ];
}