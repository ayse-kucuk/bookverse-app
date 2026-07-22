<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BookUser;

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
                ->withPivot(['status', 'rating', 'is_protected'])
                ->withTimestamps();
   }

    public function scopeWithRatingStats($query)
    {
        return $query
            ->select('books.*')
            ->withCount(['users as ratings_count' => function ($q) {
                $q->whereNotNull('book_user.rating');
            }])
            ->addSelect([
                'average_rating' => BookUser::query()
                    ->selectRaw('round(avg(rating), 1)')
                    ->whereColumn('book_user.book_id', 'books.id')
                    ->whereNotNull('rating')
                    ->limit(1),
            ]);
    }

    public function scopeMatchingSearchTerm($query, string $term)
    {
        $term = mb_strtolower(trim($term));

        if ($term === '') {
            return $query;
        }

        $pattern = '%'.$term.'%';

        return $query->where(function ($builder) use ($pattern) {
            $builder->whereRaw('LOWER(title) LIKE ?', [$pattern])
                ->orWhereRaw('LOWER(author) LIKE ?', [$pattern]);
        });
    }

    public function formattedAverageRating(): ?string
    {
        if (! $this->average_rating) {
            return null;
        }

        return number_format((float) $this->average_rating, 1);
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