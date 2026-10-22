<?php

namespace App\Services\Preferences;

use Illuminate\Validation\ValidationException;

class HexadScoringService
{
    public const SCORING_VERSION = 'hexad12-v1';

    public const ITEMS = [
        'hexad_p1' => 'philanthropist',
        'hexad_p4' => 'philanthropist',
        'hexad_s2' => 'socializer',
        'hexad_s4' => 'socializer',
        'hexad_f1' => 'free_spirit',
        'hexad_f3' => 'free_spirit',
        'hexad_a2' => 'achiever',
        'hexad_a4' => 'achiever',
        'hexad_r2' => 'player',
        'hexad_r4' => 'player',
        'hexad_d3' => 'disruptor',
        'hexad_d4' => 'disruptor',
    ];

    public function score(array $answers): array
    {
        if (array_diff(array_keys($answers), array_keys(self::ITEMS))) {
            throw ValidationException::withMessages([
                'answers' => t('preferences.onboarding.v1_0.errors.unknown_question'),
            ]);
        }

        $normalized = [];
        $errors = [];

        foreach (self::ITEMS as $code => $dimension) {
            if (!array_key_exists($code, $answers) || $answers[$code] === null || $answers[$code] === '') {
                $errors['answers.'.$code] = t('preferences.onboarding.v1_0.errors.incomplete_profile');

                continue;
            }

            $value = $answers[$code];
            $valid = (is_int($value) && $value >= 1 && $value <= 7)
                || (is_string($value) && preg_match('/^[1-7]$/D', $value) === 1);

            if (!$valid) {
                $errors['answers.'.$code] = t('preferences.onboarding.v1_0.errors.invalid_profile_value');

                continue;
            }

            $normalized[$code] = (int) $value;
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $scores = array_fill_keys(array_unique(array_values(self::ITEMS)), 0);

        foreach (self::ITEMS as $code => $dimension) {
            $scores[$dimension] += $normalized[$code];
        }

        return [
            'scoring_version' => self::SCORING_VERSION,
            'profile_scores' => $scores,
            'dominant_player_types' => array_keys($scores, max($scores), true),
            'normalized_answers' => $normalized,
        ];
    }
}
