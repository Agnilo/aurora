<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GoalPriority extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'color', 
        'order'
    ];

    public function goals()
    {
        return $this->hasMany(Goal::class, 'priority_id');
    }
}
