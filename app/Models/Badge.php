<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'icon',
        'condition',
    ];

    protected $casts = [
        'condition' => 'array',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('awarded_at')
            ->withTimestamps();
    }
}
