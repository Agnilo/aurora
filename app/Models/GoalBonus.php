<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoalBonus extends Model
{
    protected $fillable = [
        'condition_type',
        'condition_value',
        'bonus_xp',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }

    public function bonus()
    {
        return $this->belongsTo(Bonus::class);
    }
}
