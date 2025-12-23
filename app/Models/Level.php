<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $fillable = [
        'level_from',
        'level_to',
        'xp_required',
        'reward_coins',
        'title',
        'translation_key',
    ];

    public function appliesTo(int $level): bool
    {
        return $level >= $this->level_from && $level <= $this->level_to;
    }

    public function hasUsers(): bool
    {
        return UserGameDetail::whereBetween('level', [
            $this->level_from,
            $this->level_to
        ])->exists();
    }

}


