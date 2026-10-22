<?php

namespace App\Models\Preferences;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentQuestion extends Model
{
    protected $fillable = [
        'code',
        'questionnaire_type',
        'questionnaire_version',
        'locale',
        'text',
        'text_key',
        'answer_type',
        'options',
        'profile_dimension',
        'sort_order',
        'is_required',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'sort_order' => 'integer',
            'is_required' => 'boolean',
        ];
    }

    public function answers(): HasMany
    {
        return $this->hasMany(UserAssessmentAnswer::class);
    }

    public function getTranslatedTextAttribute(): string
    {
        $fallback = (string) $this->text;

        return $this->text_key ? t($this->text_key, [], $fallback) : $fallback;
    }

    public function getTranslatedOptionsAttribute(): array
    {
        return array_map(function (array $option): array {
            if (!empty($option['label_key'])) {
                $option['label'] = t(
                    $option['label_key'],
                    [],
                    (string) ($option['label'] ?? '')
                );
            }

            return $option;
        }, $this->options ?? []);
    }
}
