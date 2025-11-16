<?php

namespace App\Models\Localization;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = [
        'code',
        'name',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
    ];

    public function translations()
    {
        return $this->hasMany(Translation::class, 'language_code', 'code');
    }
}
