<?php

namespace App\Models\Preferences;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    public const XP_MANUAL = 'manual';

    public const XP_SYSTEM = 'system';

    public const GAMIFICATION_HIDDEN = 'hidden';

    public const GAMIFICATION_SUBTLE = 'subtle';

    public const GAMIFICATION_PROMINENT = 'prominent';

    protected $fillable = [
        'user_id',
        'xp_mode',
        'gamification_level',
        'show_streak',
        'priority_category_ids',
        'confirmed_at',
        'onboarding_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'show_streak' => 'boolean',
            'priority_category_ids' => 'array',
            'confirmed_at' => 'datetime',
            'onboarding_completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activeAssessment(): BelongsTo
    {
        return $this->belongsTo(UserAssessment::class, 'active_assessment_id');
    }
}
