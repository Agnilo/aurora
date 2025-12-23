<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    protected $fillable = [
        'key',
        'bonus_context_id',
        'label',
        'type',
        'value',
        'streak_days',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'value'  => 'float',
    ];

    public function goals()
    {
        return $this->hasMany(GoalBonus::class);
    }

    public function bonusContext()
    {
        return $this->belongsTo(BonusContext::class, 'bonus_context_id');
    }
}
