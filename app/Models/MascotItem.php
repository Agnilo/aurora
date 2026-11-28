<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MascotItem extends Model
{
    protected $fillable = ['slot', 'key', 'translation_key', 'layers', 'hair_base_color', 'hair_colors', 'color', 'price', 'active', 'sort_order'];

    protected $casts = ['layers' => 'array', 'hair_colors' => 'array', 'price' => 'integer', 'active' => 'boolean'];
}
