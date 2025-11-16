<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'milestone_id',
        'category_id',
        'status_id',
        'type_id',
        'priority_id',
        'title',
        'description',
        'points',
        'completed_at',
        'is_favorite',
        'is_important',
    ];

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
}
