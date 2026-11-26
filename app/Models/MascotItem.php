<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MascotItem extends Model
{
    protected $fillable = ['slot', 'key', 'translation_key', 'layers', 'color', 'price', 'active', 'sort_order'];

    protected $casts = ['layers' => 'array', 'price' => 'integer', 'active' => 'boolean'];
}
