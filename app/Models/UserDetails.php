<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDetails extends Model
{
    protected $table = 'user_details';

    protected $fillable = [
        'user_id',
        'birthdate',
        'gender',
        'description',
        'handle',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'hashtags' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
