<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserGameDetail extends Model
{
    use HasFactory;

    protected $table = 'user_game_details';

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

    protected $casts = [
        'last_activity_date' => 'date',
    ];

    protected $appends = ['xp_percent'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getXpPercentAttribute(): float
    {
        if ($this->xp_next <= 0) {
            return 0;
        }

        return min(
            100,
            round(($this->xp / $this->xp_next) * 100, 2)
        );
    }
}
