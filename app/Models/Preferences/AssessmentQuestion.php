<?php

namespace App\Models\Preferences;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AssessmentQuestion extends Model
{
    public const ONBOARDING_V1_PREFIX = 'preferences.onboarding.v1_0';

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
        $key = $this->text_key;
        if (!$key && $this->usesOnboardingV1()) {
            $key = self::ONBOARDING_V1_PREFIX . '.questions.' . $this->code;
        }

        return $key ? t($key, (string) $this->text) : (string) $this->text;
    }

    public function getTranslatedOptionsAttribute(): array
    {
        return array_map(function (array $option): array {
            $label = (string) ($option['label'] ?? '');
            $key = $option['label_key'] ?? null;
            if (!$key && $this->usesOnboardingV1() && isset($option['value'])) {
                $value = $option['value'];
                $key = match (true) {
                    $this->answer_type === 'likert' => self::ONBOARDING_V1_PREFIX . '.likert.' . $value,
                    $this->code === 'priority_categories' && is_numeric($value) =>
                        'lookup.categories.category.' . Str::slug($label, '_'),
                    default => self::ONBOARDING_V1_PREFIX . '.options.' . $this->code . '.' . $value,
                };
            }
            if ($key) {
                $option['label'] = t($key, $label);
            }

            return $option;
        }, $this->options ?? []);
    }

    private function usesOnboardingV1(): bool
    {
        return $this->questionnaire_type === UserAssessment::TYPE_ONBOARDING
            && $this->questionnaire_version === '1.0';
    }
}
