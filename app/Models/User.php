<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

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
        'reading_goal',
        'reading_goal_year',
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
            'is_admin' => 'boolean',
        ];
    }

    public function books()
    {
        return $this->belongsToMany(Book::class, 'book_user')->withPivot(['status', 'rating', 'is_protected'])->withTimestamps();
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

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->unread()->count();
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

    public function scopeVisibleTo($query, ?User $viewer)
    {
        if (! $viewer) {
            return $query->where('account_visibility', self::VISIBILITY_PUBLIC);
        }

        $followingIds = $viewer->following()->pluck('users.id');

        return $query->where(function ($inner) use ($viewer, $followingIds) {
            $inner->where('users.id', $viewer->id)
                ->orWhere('account_visibility', self::VISIBILITY_PUBLIC)
                ->orWhere(function ($private) use ($followingIds) {
                    $private->where('account_visibility', self::VISIBILITY_FOLLOWERS)
                        ->whereIn('users.id', $followingIds);
                });
        });
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

    public function booksReadInYear(?int $year = null): int
    {
        $year ??= now()->year;

        return $this->books()
            ->wherePivot('status', 'okundu')
            ->whereYear('book_user.updated_at', $year)
            ->count();
    }

    public function hasActiveReadingGoal(): bool
    {
        return $this->reading_goal
            && $this->reading_goal_year === now()->year;
    }

    public static function profilePhotosDisk(): string
    {
        $configured = config('filesystems.profile_photos_disk', 'public');

        // Explicit override from env.
        if ($configured && $configured !== 'public') {
            return $configured;
        }

        // Auto-use Supabase when S3 credentials are present (production).
        if (config('filesystems.disks.supabase.key')
            && config('filesystems.disks.supabase.secret')
            && config('filesystems.disks.supabase.bucket')
            && config('filesystems.disks.supabase.endpoint')) {
            return 'supabase';
        }

        return 'public';
    }

    public function profilePhotoUrl(): ?string
    {
        if (! $this->profile_photo_path) {
            return null;
        }

        if (str_starts_with($this->profile_photo_path, 'http://')
            || str_starts_with($this->profile_photo_path, 'https://')) {
            return $this->appendCacheBuster($this->profile_photo_path);
        }

        $disk = static::profilePhotosDisk();

        if ($disk === 'public') {
            $url = asset('storage/'.$this->profile_photo_path);
        } else {
            $base = rtrim((string) config("filesystems.disks.{$disk}.url"), '/');
            $url = $base !== ''
                ? $base.'/'.ltrim($this->profile_photo_path, '/')
                : Storage::disk($disk)->url($this->profile_photo_path);
        }

        return $this->appendCacheBuster($url);
    }

    private function appendCacheBuster(string $url): string
    {
        $version = $this->updated_at?->timestamp ?? time();

        return $url.(str_contains($url, '?') ? '&' : '?').'v='.$version;
    }

    /**
     * @return array{year: int, target: int|null, current: int, percentage: int, remaining: int|null, completed: bool}
     */
    public function readingGoalStats(): array
    {
        $year = now()->year;
        $current = $this->booksReadInYear($year);
        $target = $this->hasActiveReadingGoal() ? (int) $this->reading_goal : null;

        if (! $target) {
            return [
                'year' => $year,
                'target' => null,
                'current' => $current,
                'percentage' => 0,
                'remaining' => null,
                'completed' => false,
            ];
        }

        $percentage = min(100, (int) round(($current / $target) * 100));
        $remaining = max(0, $target - $current);

        return [
            'year' => $year,
            'target' => $target,
            'current' => $current,
            'percentage' => $percentage,
            'remaining' => $remaining,
            'completed' => $current >= $target,
        ];
    }
}
