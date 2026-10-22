<?php

namespace App\Services\Preferences;

use App\Models\Preferences\UserAssessment;
use App\Models\Preferences\UserPreference;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use LogicException;

class MotivationProfileService
{
    /** Activate a completed, owned profile without treating settings as confirmed. */
    public function activate(User $user, UserAssessment $assessment): UserAssessment
    {
        return DB::transaction(function () use ($user, $assessment) {
            User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();
            $record = $user->assessments()->whereKey($assessment->getKey())->firstOrFail();

            if (!$record->completed_at || !$record->scoring_version) {
                throw new LogicException('Only a completed, scored assessment can become active.');
            }

            $scores = $record->profile_scores ?? [];
            if (count($scores) !== count(UserAssessment::PLAYER_TYPES)
                || array_diff(UserAssessment::PLAYER_TYPES, array_keys($scores))) {
                throw new LogicException('The assessment must contain all six player-type scores.');
            }

            // Recommendations are not confirmed choices. Activation only links the profile.
            $preferences = UserPreference::firstOrCreate(['user_id' => $user->getKey()]);
            $preferences->forceFill([
                'active_assessment_id' => $record->getKey(),
                'onboarding_completed_at' => $preferences->onboarding_completed_at ?? $record->completed_at,
            ])->save();

            $user->unsetRelation('preferences');

            return $record;
        });
    }

    /** Always scope the active assessment to its owner, even if the pointer is invalid. */
    public function active(User $user): ?UserAssessment
    {
        $assessmentId = $user->preferences()->value('active_assessment_id');
        if (!$assessmentId) {
            return null;
        }

        return $user->assessments()
            ->whereKey($assessmentId)
            ->whereNotNull('completed_at')
            ->whereNotNull('scoring_version')
            ->whereNotNull('profile_scores')
            ->first();
    }

    /** Raw 2–14 scores, not percentages or probabilities. Empty means no profile. */
    public function scores(User $user): array
    {
        return $this->active($user)?->profile_scores ?? [];
    }
}
