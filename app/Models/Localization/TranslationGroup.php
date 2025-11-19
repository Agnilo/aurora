<?php

namespace App\Models\Localization;

use Illuminate\Database\Eloquent\Model;
use App\Models\Localization\Translation;

class TranslationGroup extends Model
{
    protected $table = 'translation_groups';

    protected $fillable = ['name', 'key'];
    
    public function translations()
    {
        return $this->hasMany(Translation::class, 'group', 'key');
    }

    public function label(): string
    {
        return Translation::where('group', 'group')
            ->where('key', $this->key . '.group')
            ->where('language_code', app()->getLocale())
            ->value('value')
            ?? $this->name;
    }
}
