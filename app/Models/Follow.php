<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Follow extends Model
{
    protected $fillable = [
        'follower_id',
        'following_id',
    ];
}
