<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Localization\Translation;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'max_points',
        'icon',
    ];

    public function goals()
    {
        return $this->hasMany(Goal::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function categoryLevels()
    {
        return $this->hasMany(CategoryLevel::class);
    }

    public function translations()
    {
        return $this->hasMany(Translation::class, 'entity_id')
            ->where('entity_type', 'categories.category');
    }

    public function translatedName()
    {
        $key = "categories.category.{$this->id}.name";
        return t($key, $this->name);
    }

    public function getTranslatedNameAttribute(): string
    {
        $slug = Str::slug($this->name, '_');

        return t("lookup.categories.category.{$slug}");
    }
}
