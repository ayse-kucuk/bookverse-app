<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    public const TYPE_FOLLOW = 'follow';

    public const TYPE_POST_LIKE = 'post_like';

    public const TYPE_POST_COMMENT = 'post_comment';

    protected $fillable = [
        'user_id',
        'actor_id',
        'type',
        'post_id',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function markAsRead(): void
    {
        if ($this->isUnread()) {
            $this->update(['read_at' => now()]);
        }
    }

    public function message(): string
    {
        return match ($this->type) {
            self::TYPE_FOLLOW => $this->actor->name.' seni takip etti',
            self::TYPE_POST_LIKE => $this->actor->name.' paylaşımını beğendi',
            self::TYPE_POST_COMMENT => $this->actor->name.' paylaşımına yorum yaptı',
            default => 'Yeni bir bildirimin var',
        };
    }

    public function url(): string
    {
        return match ($this->type) {
            self::TYPE_FOLLOW => route('users.show', $this->actor),
            self::TYPE_POST_LIKE, self::TYPE_POST_COMMENT => $this->post_id
                ? route('posts.show', $this->post_id).'#comments'
                : route('profile'),
            default => route('home'),
        };
    }

    public static function recordFollow(User $recipient, User $actor): void
    {
        if ($recipient->id === $actor->id) {
            return;
        }

        static::firstOrCreate([
            'user_id' => $recipient->id,
            'actor_id' => $actor->id,
            'type' => self::TYPE_FOLLOW,
            'post_id' => null,
        ]);
    }

    public static function recordPostLike(Post $post, User $actor): void
    {
        if ($post->user_id === $actor->id) {
            return;
        }

        static::firstOrCreate([
            'user_id' => $post->user_id,
            'actor_id' => $actor->id,
            'type' => self::TYPE_POST_LIKE,
            'post_id' => $post->id,
        ]);
    }

    public static function removePostLike(Post $post, User $actor): void
    {
        static::where([
            'user_id' => $post->user_id,
            'actor_id' => $actor->id,
            'type' => self::TYPE_POST_LIKE,
            'post_id' => $post->id,
        ])->delete();
    }

    public static function recordPostComment(Post $post, User $actor): void
    {
        if ($post->user_id === $actor->id) {
            return;
        }

        static::updateOrCreate(
            [
                'user_id' => $post->user_id,
                'actor_id' => $actor->id,
                'type' => self::TYPE_POST_COMMENT,
                'post_id' => $post->id,
            ],
            ['read_at' => null]
        );
    }
}
