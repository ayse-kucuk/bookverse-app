<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function ($book) {
        // Eğer is_protected değeri atanmamışsa true yap, atanmışsa dokunma
        if (!isset($book->is_protected)) {
            $book->is_protected = true;
        }
    });

    static::deleting(function ($book) {
        return !$book->is_protected;
    });
    }

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
        'category_id', 
        'title',
        'author',
        'image_url',
        'description',
        'page_count',
        'cover_image',
        'is_protected'
    ];
}