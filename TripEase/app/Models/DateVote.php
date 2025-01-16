<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DateVote extends Model
{
    use HasFactory;

    protected $fillable = ['trip_id', 'date_id', 'user_id', 'judgement'];

    public function candidateDate()
    {
        return $this->belongsTo(candidateDate::class, 'date_id', 'id');
    }
}
