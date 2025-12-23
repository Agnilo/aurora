<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BadgeCategory extends Model
{
    protected $fillable = [
        'key',
        'label',
        'active',
    ];

    public function badges()
    {
        return $this->hasMany(
            Badge::class,
            'badge_category_id',
            'id'
        );
    }
}
