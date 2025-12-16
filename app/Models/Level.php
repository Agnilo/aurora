<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $fillable = [
        'level',
        'xp_required',
    ];

    public $timestamps = true;

    public function rewardBonus()
    {
        return $this->belongsTo(Bonus::class, 'reward_bonus_id');
    }
}
