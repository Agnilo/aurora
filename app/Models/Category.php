<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
}
