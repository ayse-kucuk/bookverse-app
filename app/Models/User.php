<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const VISIBILITY_PUBLIC = 'public';

    public const VISIBILITY_FOLLOWERS = 'followers_only';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_photo_path',
        'is_admin',
        'account_visibility',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function books()
    {
        return $this->belongsToMany(Book::class, 'book_user')->withPivot(['status', 'is_protected'])->withTimestamps();
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class)->latest();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')->withTimestamps();
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')->withTimestamps();
    }

    public function isPublic(): bool
    {
        return $this->account_visibility === self::VISIBILITY_PUBLIC;
    }

    public function isFollowersOnly(): bool
    {
        return $this->account_visibility === self::VISIBILITY_FOLLOWERS;
    }

    public function isFollowedBy(?User $viewer): bool
    {
        if (! $viewer) {
            return false;
        }

        if ($viewer->id === $this->id) {
            return true;
        }

        return $this->followers()->where('users.id', $viewer->id)->exists();
    }

    public function canBeViewedBy(?User $viewer): bool
    {
        if (! $viewer) {
            return $this->isPublic();
        }

        if ($viewer->id === $this->id) {
            return true;
        }

        if ($this->isPublic()) {
            return true;
        }

        return $this->isFollowedBy($viewer);
    }

    public function follow(User $user): void
    {
        if ($this->id === $user->id) {
            return;
        }

        $this->following()->syncWithoutDetaching([$user->id]);
    }

    public function unfollow(User $user): void
    {
        $this->following()->detach($user->id);
    }
}
