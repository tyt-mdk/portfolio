<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TripRequest extends Model
{
    protected $fillable = [
        'trip_id',
        'user_id',
        'content',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(RequestComment::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(RequestLike::class);
    }
}