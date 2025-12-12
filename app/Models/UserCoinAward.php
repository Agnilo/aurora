<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCoinAward extends Model
{
        protected $fillable = [
        'user_id',
        'award_type',
        'awardable_id',
        'coins',
        'awarded_at',
    ];
}
