<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GoalType extends Model
{
    use HasFactory;

    protected $table = 'goal_types';

    protected $fillable = [
        'name',
    ];

    public function goals()
    {
        return $this->hasMany(Goal::class, 'type_id');
    }
}
