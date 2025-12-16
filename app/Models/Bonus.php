<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    protected $fillable = [
        'key',
        'label',
        'type',
        'value',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function goals()
    {
        return $this->hasMany(GoalBonus::class);
    }
}
