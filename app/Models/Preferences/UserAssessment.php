<?php

namespace App\Models\Preferences;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserAssessment extends Model
{
    public const TYPE_ONBOARDING = 'onboarding';

    public const TYPE_CHECK_IN = 'check_in';

    public const PLAYER_TYPES = [
        'philanthropist',
        'socializer',
        'free_spirit',
        'achiever',
        'player',
        'disruptor',
    ];

    protected $fillable = [
        'user_id',
        'type',
        'questionnaire_version',
        'locale',
        'scoring_version',
        'profile_scores',
        'dominant_player_types',
        'recommended_preferences',
        'applied_preferences',
        'started_at',
        'completed_at',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'profile_scores' => 'array',
            'dominant_player_types' => 'array',
            'recommended_preferences' => 'array',
            'applied_preferences' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'applied_at' => 'datetime',
        ];
    }

    public function effectiveSurveyPreferences(): array
    {
        $recommendations = $this->recommended_preferences;
        if (empty($recommendations) && $this->completed_at) {
            $previous = self::query()
                ->where('user_id', $this->user_id)
                ->where('type', self::TYPE_ONBOARDING)
                ->whereNotNull('recommended_preferences')
                ->where(function ($query) {
                    $query->where('completed_at', '<', $this->completed_at)
                        ->orWhere(function ($sameTime) {
                            $sameTime->where('completed_at', $this->completed_at)->where('id', '<', $this->id);
                        });
                })
                ->orderByDesc('completed_at')->orderByDesc('id')
                ->get(['recommended_preferences'])
                ->first(fn($item) => !empty($item->recommended_preferences));
            $recommendations = $previous?->recommended_preferences;
        }

        $recommendations ??= [];
        if (($recommendations['gamification_level'] ?? null) === UserPreference::GAMIFICATION_HIDDEN) {
            $recommendations['xp_mode'] = UserPreference::XP_SYSTEM;
            $recommendations['show_streak'] = false;
        }
        return $recommendations;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(UserAssessmentAnswer::class);
    }
}
