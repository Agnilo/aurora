<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GoalStatus extends Model
{
    use HasFactory;

    protected $table = 'goal_statuses';

    protected $fillable = [
        'name',
    ];

    public function goals()
    {
        return $this->hasMany(Goal::class, 'status_id');
    }
}
