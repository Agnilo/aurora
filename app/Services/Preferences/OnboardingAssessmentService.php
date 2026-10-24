<?php

namespace App\Services\Preferences;

use App\Models\Category;
use App\Models\Preferences\AssessmentQuestion;
use App\Models\Preferences\UserAssessment;
use App\Models\Preferences\UserPreference;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

class OnboardingAssessmentService
{
    public const QUESTIONNAIRE_VERSION = '1.0';

    public const SOURCE_LOCALE = 'lt';

    private const PREFERENCE_CODES = [
        'gamification_level', 'xp_mode', 'show_streak', 'priority_categories',
    ];

    public function __construct(private readonly HexadScoringService $scorer)
    {
    }

    public function start(User $user): UserAssessment
    {
        if (!$user->exists) {
            throw new AuthorizationException;
        }

        return $user->assessments()->firstOrCreate([
            'type' => UserAssessment::TYPE_ONBOARDING,
            'questionnaire_version' => self::QUESTIONNAIRE_VERSION,
            'locale' => app()->getLocale(),
            'completed_at' => null,
        ], [
            'started_at' => now(),
        ]);
    }

    public function complete(
        User $user,
        UserAssessment $assessment,
        array $answers,
        bool $includeProfile = true,
        array $questionOrder = []
    ): UserAssessment {
        return DB::transaction(function () use ($user, $assessment, $answers, $includeProfile, $questionOrder) {
            // Scope the lookup to the acting user; another user's attempt is never writable.
            $record = $user->assessments()->whereKey($assessment->getKey())
                ->lockForUpdate()->firstOrFail();

            if ($record->completed_at !== null) {
                $this->fail('assessment', 'already_completed');
            }

            if ($record->type !== UserAssessment::TYPE_ONBOARDING
                || $record->questionnaire_version !== self::QUESTIONNAIRE_VERSION) {
                $this->fail('assessment', 'unsupported_assessment');
            }

            $prepared = $this->prepareAnswers($answers, $includeProfile);
            $questions = $prepared['questions'];
            $normalized = $prepared['normalized'];
            $recommended = $prepared['recommended'];
            $profile = $prepared['profile'];

            $positions = $this->positions($questionOrder, $questions, array_keys($normalized));
            $questionIds = [];

            // Validation finishes before any draft answers are changed.
            foreach ($normalized as $code => $value) {
                $questionId = $questions[$code]->getKey();
                $questionIds[] = $questionId;
                $record->answers()->updateOrCreate([
                    'assessment_question_id' => $questionId,
                ], [
                    'value' => $value,
                    'position' => $positions[$code] ?? null,
                    'answered_at' => now(),
                ]);
            }

            // Remove only obsolete answers from this unfinished draft.
            $record->answers()->whereNotIn('assessment_question_id', $questionIds)->delete();

            $record->update([
                'locale' => app()->getLocale(),
                'scoring_version' => $profile['scoring_version'] ?? null,
                'profile_scores' => $profile['profile_scores'] ?? null,
                'dominant_player_types' => $profile['dominant_player_types'] ?? null,
                'recommended_preferences' => $recommended,
                'started_at' => $record->started_at ?? now(),
                'completed_at' => now(),
            ]);

            if ($profile !== null) {
                app(MotivationProfileService::class)->activate($user, $record);
            }

            return $record->fresh(['answers.assessmentQuestion']);
        });
    }

    /** Validate without writing any records; also used before guest registration. */
    public function validateAnswers(array $answers): void
    {
        $this->prepareAnswers($answers, true);
    }

    private function prepareAnswers(array $answers, bool $includeProfile): array
    {
        $questions = $this->questions();
        $unknown = array_diff(array_keys($answers), $questions->keys()->all());

        if ($unknown) {
            $this->fail('answers', 'unknown_question');
        }

        $gamification = $this->choice($questions['gamification_level'], $answers);
        $categoryAnswers = $this->categories($questions['priority_categories'], $answers);
        $normalized = [
            'gamification_level' => $gamification,
            'priority_categories' => $categoryAnswers,
        ];
        $recommended = [
            'gamification_level' => match ($gamification) {
                'hidden' => UserPreference::GAMIFICATION_HIDDEN,
                'subtle', 'unsure' => UserPreference::GAMIFICATION_SUBTLE,
                'prominent' => UserPreference::GAMIFICATION_PROMINENT,
                default => throw new LogicException('Unsupported gamification option.'),
            },
            'priority_category_ids' => $categoryAnswers === ['none'] ? [] : $categoryAnswers,
        ];

        if ($gamification === 'hidden') {
            // Hidden fields are not treated as confirmed XP/streak choices.
            $recommended['show_streak'] = false;
        } else {
            $xpMode = $this->choice($questions['xp_mode'], $answers);
            $streak = $this->choice($questions['show_streak'], $answers);
            $normalized['xp_mode'] = $xpMode;
            $normalized['show_streak'] = $streak;
            $recommended['xp_mode'] = match ($xpMode) {
                'manual' => UserPreference::XP_MANUAL,
                'system', 'unsure' => UserPreference::XP_SYSTEM,
                default => throw new LogicException('Unsupported XP option.'),
            };
            $recommended['show_streak'] = match ($streak) {
                'show', 'unsure' => true,
                'hide' => false,
                default => throw new LogicException('Unsupported streak option.'),
            };
        }

        $profile = null;

        if ($includeProfile) {
            $profile = $this->scorer->score(array_intersect_key($answers, HexadScoringService::ITEMS));
            $normalized += $profile['normalized_answers'];
        }

        return compact('questions', 'normalized', 'recommended', 'profile');
    }

    private function questions(): Collection
    {
        $questions = AssessmentQuestion::query()
            ->where('questionnaire_type', UserAssessment::TYPE_ONBOARDING)
            ->where('questionnaire_version', self::QUESTIONNAIRE_VERSION)
            ->where('locale', self::SOURCE_LOCALE)
            ->get()->keyBy('code');
        $expected = array_merge(self::PREFERENCE_CODES, array_keys(HexadScoringService::ITEMS));

        if ($questions->count() !== count($expected)
            || array_diff($expected, $questions->keys()->all())) {
            throw new LogicException('The onboarding question catalog is missing or inconsistent.');
        }

        foreach (HexadScoringService::ITEMS as $code => $dimension) {
            $question = $questions[$code];

            if ($question->profile_dimension !== $dimension
                || $question->answer_type !== 'likert'
                || array_column($question->options ?? [], 'value') !== range(1, 7)) {
                throw new LogicException('The Hexad question catalog does not match scoring version '.HexadScoringService::SCORING_VERSION);
            }
        }

        return $questions;
    }

    private function choice(AssessmentQuestion $question, array $answers): string
    {
        if (!array_key_exists($question->code, $answers)) {
            $this->fail('answers.'.$question->code, 'required_answer');
        }

        $value = $answers[$question->code];

        if (!is_string($value) || !in_array($value, array_column($question->options ?? [], 'value'), true)) {
            $this->fail('answers.'.$question->code, 'invalid_choice');
        }

        return $value;
    }

    private function categories(AssessmentQuestion $question, array $answers): array
    {
        $field = 'answers.'.$question->code;
        $values = $answers[$question->code] ?? null;

        if (!is_array($values) || !array_is_list($values) || $values === []) {
            $this->fail($field, 'required_answer');
        }

        if (in_array('none', $values, true)) {
            if ($values !== ['none']) {
                $this->fail($field, 'categories_none_exclusive');
            }

            return ['none'];
        }

        $normalized = [];

        foreach ($values as $value) {
            if (!(is_int($value) && $value > 0)
                && !(is_string($value) && preg_match('/^[1-9][0-9]*$/D', $value) === 1
                    && (string) (int) $value === $value)) {
                $this->fail($field, 'invalid_categories');
            }

            $normalized[] = (int) $value;
        }

        $allowed = array_column($question->options ?? [], 'value');

        if (count(array_unique($normalized)) !== count($normalized)
            || array_diff($normalized, $allowed)
            || Category::query()->whereIn('id', $normalized)->count() !== count($normalized)) {
            $this->fail($field, 'invalid_categories');
        }

        return $normalized;
    }

    private function positions(array $order, Collection $questions, array $answeredCodes): array
    {
        if ($order === []) {
            return [];
        }

        if (!array_is_list($order)) {
            $this->fail('answers', 'invalid_order');
        }

        $positions = [];

        foreach ($order as $index => $code) {
            if (!is_string($code) || !$questions->has($code) || isset($positions[$code])) {
                $this->fail('answers', 'invalid_order');
            }

            $positions[$code] = $index + 1;
        }

        if (array_diff($answeredCodes, array_keys($positions))) {
            $this->fail('answers', 'invalid_order');
        }

        return $positions;
    }

    private function fail(string $field, string $key): never
    {
        throw ValidationException::withMessages([
            $field => t('preferences.onboarding.v1_0.errors.'.$key),
        ]);
    }
}
