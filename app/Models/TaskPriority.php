<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaskPriority extends Model
{
    use HasFactory;

    protected $table = 'task_priorities';

    protected $fillable = [
        'name',
    ];

    public function tasks()
    {
        return $this->hasMany(Task::class, 'priority_id');
    }
}
