<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

class Habit extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['weekdays' => 'array', 'starts_on' => 'date', 'ends_on' => 'date', 'archived_at' => 'datetime'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function priority()
    {
        return $this->belongsTo(TaskPriority::class, 'priority_id');
    }
    public function completions()
    {
        return $this->hasMany(HabitCompletion::class);
    }
    public function today(): CarbonImmutable { return CarbonImmutable::now($this->timezone)->startOfDay(); }
    public function period(): string
    {
        return ($this->frequency === 'weekly' ? $this->today()->startOfWeek(1) : $this->today())->toDateString();
    }
    public function dueToday(): bool
    {
        $today = $this->today();
        return !$this->archived_at && $today->toDateString() >= $this->starts_on->toDateString()
            && (!$this->ends_on || $today->toDateString() <= $this->ends_on->toDateString())
            && ($this->frequency === 'weekly' || in_array($today->dayOfWeekIso, $this->weekdays ?? [], true));
    }
}
