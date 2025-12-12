<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStreak extends Model
{
        protected $fillable = [
        'user_id',
        'activity_date',
        'streak_day',
    ];

    protected $casts = [
        'activity_date' => 'date',
    ];
}
