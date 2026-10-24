<?php

namespace App\Services\Preferences;

use App\Models\User;

class StreakVisibilityService
{
    public function visible(User $user): bool
    {
        $user->loadMissing('preferences');

        // A later explicit choice takes precedence over the onboarding answer.
        if ($user->preferences?->show_streak !== null) {
            return $user->preferences->show_streak;
        }

        $assessment = app(MotivationProfileService::class)->active($user);

        if ($assessment === null) {
            return true;
        }

        $answer = $assessment->answers()
            ->whereHas('assessmentQuestion', fn ($query) => $query->where('code', 'show_streak'))
            ->first()?->value;

        return match ($answer) {
            'hide' => false,
            'show', 'unsure' => true,
            // The hidden-gamification path never asks the streak question.
            default => $assessment->recommended_preferences['show_streak'] ?? true,
        };
    }
}
