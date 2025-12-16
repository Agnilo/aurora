<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XpRule extends Model
{
    protected $fillable = [
        'key',
        'label',
        'xp',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
