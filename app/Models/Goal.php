<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'status_id',
        'type_id',
        'priority_id',
        'title',
        'description',
        'color',
        'deadline',
        'start_date',
        'end_date',
        'progress',
        'is_completed',
        'is_favorite',
        'is_important',
        'visibility',
        'reminder_date',
        'tags',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'is_favorite' => 'boolean',
        'is_important' => 'boolean',
        'reminder_date' => 'datetime',
        'tags' => 'array',
        'deadline' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

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
        return $this->belongsTo(GoalPriority::class, 'priority_id');
    }

    public function status()
    {
        return $this->belongsTo(GoalStatus::class, 'status_id');
    }

    public function type()
    {
        return $this->belongsTo(GoalType::class, 'type_id');
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }

    public function recalculateProgress()
    {
        $totalPoints = $this->milestones->flatMap->tasks->sum('points');
        $donePoints = $this->milestones->flatMap->tasks->whereNotNull('completed_at')->sum('points');

        $progress = $totalPoints ? round(($donePoints / $totalPoints) * 100) : 0;

        $this->progress = $progress;
        $this->is_completed = ($progress >= 100);
        $this->save();

        return $progress;
    }
}
