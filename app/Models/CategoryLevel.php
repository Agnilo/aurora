<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoryLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'level',
        'xp',
        'xp_next',
    ];

    protected $casts = [
        'level' => 'integer',
        'xp' => 'integer',
        'xp_next' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getProgressPercentAttribute(): float
    {
        $xp  = $this->xp ?? 0;
        $max = $this->xp_next ?? 100;

        if ($max <= 0) return 0;

        return max(0, min(100, ($xp / $max) * 100));
    }

    public function getFullSquaresAttribute(): int
    {
        return (int) floor($this->progress_percent / 10);
    }

    public function getPartialFillAttribute(): int
    {
        return (int) round(fmod($this->progress_percent, 10) * 10);
    }
    
    public function getLevelNameAttribute(): string
    {
        return match (true) {
            $this->level <= 1 => 'Seed',
            $this->level <= 2 => 'Growing',
            $this->level <= 4 => 'Stable',
            default           => 'Thriving',
        };
    }

}
