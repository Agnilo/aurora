<?php

namespace App\Models\Preferences;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAssessmentAnswer extends Model
{
    protected $fillable = [
        'user_assessment_id',
        'assessment_question_id',
        'value',
        'position',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'json',
            'position' => 'integer',
            'answered_at' => 'datetime',
        ];
    }

    public function userAssessment(): BelongsTo
    {
        return $this->belongsTo(UserAssessment::class);
    }

    public function assessmentQuestion(): BelongsTo
    {
        return $this->belongsTo(AssessmentQuestion::class);
    }
}
