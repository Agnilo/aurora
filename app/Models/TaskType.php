<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaskType extends Model
{
    use HasFactory;

    protected $table = 'task_types';

    protected $fillable = [
        'name',
    ];

    public function tasks()
    {
        return $this->hasMany(Task::class, 'type_id');
    }
}
