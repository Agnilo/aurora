<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $fillable = [
        'badge_category_id',
        'key',
        'name',
        'description',
        'icon_path',
        'condition',
    ];

    protected $casts = [
        'condition' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(
            BadgeCategory::class,
            'badge_category_id',
            'id'
        );
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('awarded_at')
            ->withTimestamps();
    }
}
