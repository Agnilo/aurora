<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMascot extends Model
{
    protected $fillable = ['user_id', 'selection'];

    protected $casts = ['selection' => 'array'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
