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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(UserAssessmentAnswer::class);
    }
}
