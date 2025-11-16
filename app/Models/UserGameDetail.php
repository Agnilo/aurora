<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserGameDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'level',
        'xp',
        'xp_next',
        'coins',
        'streak_current',
        'streak_best',
        'last_activity_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
