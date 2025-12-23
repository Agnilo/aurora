<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonusContext extends Model
{
    protected $fillable = [
        'key',
        'label',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function bonuses()
    {
        return $this->hasMany(Bonus::class);
    }

}
