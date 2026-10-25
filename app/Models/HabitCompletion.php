<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HabitCompletion extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['period_start' => 'date', 'completed_at' => 'datetime'];
    public function setPeriodStartAttribute($value): void
    {
        $this->attributes['period_start'] = \Carbon\Carbon::parse($value)->toDateString();
    }

    public function habit()
    {
        return $this->belongsTo(Habit::class);
    }
}
