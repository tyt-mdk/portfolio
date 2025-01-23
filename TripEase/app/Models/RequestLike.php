<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestLike extends Model
{
    protected $fillable = [
        'trip_request_id',
        'user_id',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(TripRequest::class, 'trip_request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}