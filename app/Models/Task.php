<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'milestone_id',
        'goal_id',
        'user_id',
        'scheduled_on',
        'archived_at',
        'category_id',
        'status_id',
        'type_id',
        'priority_id',
        'title',
        'description',
        'points',
        'manual_points',
        'completed_at',
        'is_favorite',
        'is_important',
    ];

    protected $casts = ['completed_at' => 'datetime', 'scheduled_on' => 'date', 'archived_at' => 'datetime'];

    protected static function booted(): void
    {
        static::saving(function (Task $task) {
            if ($task->milestone_id) {
                $task->goal_id = Milestone::findOrFail($task->milestone_id)->goal_id;
            }
            if ($task->goal_id) {
                $task->user_id = Goal::findOrFail($task->goal_id)->user_id;
            }
        });
    }

    public function scopeForGoalProgress($query)
    {
        return $query->where(fn($q) => $q->whereNull('archived_at')->orWhereNotNull('completed_at'));
    }

    public function scopeOwnedBy($query, $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }

    public function milestone()
    {
        return $this->belongsTo(Milestone::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function status()
    {
        return $this->belongsTo(TaskStatus::class, 'status_id');
    }

    public function type()
    {
        return $this->belongsTo(TaskType::class, 'type_id');
    }

    public function priority()
    {
        return $this->belongsTo(TaskPriority::class, 'priority_id');
    }

    public function pointsLog()
    {
        return $this->hasOne(PointsLog::class);
    }
}
