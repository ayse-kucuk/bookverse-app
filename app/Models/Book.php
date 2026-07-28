<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\BookUser;

class Book extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Book $book) {
            if (! isset($book->is_protected)) {
                $book->is_protected = true;
            }

            if (blank($book->slug)) {
                $book->slug = static::generateUniqueSlug($book->title, $book->author);
            }
        });

        static::created(function (Book $book) {
            if (blank($book->slug)) {
                $book->forceFill([
                    'slug' => static::generateUniqueSlug($book->title, $book->author, $book->id),
                ])->saveQuietly();
            }
        });

        static::updating(function (Book $book) {
            if ($book->isDirty(['title', 'author']) && blank($book->slug)) {
                $book->slug = static::generateUniqueSlug($book->title, $book->author, $book->id);
            }

            if ($book->isDirty(['title', 'author']) && ! $book->isDirty('slug')) {
                $book->slug = static::generateUniqueSlug($book->title, $book->author, $book->id);
            }
        });

        static::deleting(function (Book $book) {
            return ! $book->is_protected;
        });
    }

    public static function generateUniqueSlug(string $title, string $author = '', ?int $ignoreId = null): string
    {
        $base = Str::slug(trim($title.' '.$author));
        if ($base === '') {
            $base = 'kitap';
        }

        $slug = $base;
        $i = 2;

        while (
            static::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

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

    public function seoDescription(): string
    {
        $text = trim((string) $this->description);

        if ($text !== '') {
            return Str::limit(strip_tags($text), 160);
        }

        return __('ui.seo.book_description', [
            'title' => $this->title,
            'author' => $this->author,
        ]);
    }

    public function publicUrl(): string
    {
        if (filled($this->slug)) {
            return route('books.show', $this->slug);
        }

        return route('books.show.legacy', $this->id);
    }

    public function getDisplayDescriptionAttribute(): string
    {
        return trans_content($this->description, 'book_description');
    }

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'author',
        'image_url',
        'description',
        'page_count',
        'cover_image',
        'is_protected',
    ];
}
