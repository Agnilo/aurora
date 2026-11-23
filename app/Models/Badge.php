<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

    public function getTranslatedNameAttribute(): string
    {
        return t('gamification.badge_name.' . Str::slug($this->name, '_'), $this->name);
    }

    public function getTranslatedDescriptionAttribute(): string
    {
        $description = (string) $this->description;

        return $description === '' ? '' : t('gamification.badge_description.' . Str::slug($description, '_'), $description);
    }

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
