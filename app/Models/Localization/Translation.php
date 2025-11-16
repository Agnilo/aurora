<?php

namespace App\Models\Localization;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $fillable = [
        'group',
        'key',
        'language_code',
        'value',
    ];

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_code', 'code');
    }
}
