<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'type',
        'content',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class);
    }

    public function scopeWithLikeMeta(Builder $query, ?User $viewer): Builder
    {
        $query->withCount(['likes', 'comments']);

        if ($viewer) {
            return $query->withExists([
                'likes as liked_by_viewer' => fn ($q) => $q->where('user_id', $viewer->id),
            ]);
        }

        return $query->withExists([
            'likes as liked_by_viewer' => fn ($q) => $q->whereRaw('1 = 0'),
        ]);
    }

    public function isQuote(): bool
    {
        return $this->type === 'quote';
    }
}
