<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Milestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'goal_id',
        'title',
        'deadline',
        'order_index',
        'progress',
        'is_completed',
    ];

    protected $casts = [
        'deadline' => 'date',
        'is_completed' => 'boolean',
    ];

    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function recalculateProgress()
    {
        $total = $this->tasks()->count();
        $done = $this->tasks()->whereNotNull('completed_at')->count();

        $progress = $total ? round(($done / $total) * 100) : 0;

        $this->progress = $progress;

        return $progress;
    }
}
