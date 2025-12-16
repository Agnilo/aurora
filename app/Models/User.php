<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted()
    {
        static::created(function ($user) {
            $user->gameDetails()->create([
                'level' => 1,
                'xp' => 0,
                'xp_next' => 100,
                'coins' => 0,
                'streak_current' => 0,
                'streak_best' => 0,
                'last_activity_date' => now(),
            ]);

            foreach (\App\Models\Category::all() as $category) {
                $user->categoryLevels()->create([
                    'category_id' => $category->id,
                    'level' => 1,
                    'xp' => 0,
                    'xp_next' => 100,
                ]);
            }
        });


    }

    public function goals()
    {
        return $this->hasMany(Goal::class);
    }

    public function gameDetails()
    {
        return $this->hasOne(UserGameDetail::class);
    }

    public function categoryLevels()
    {
        return $this->hasMany(CategoryLevel::class);
    }

    public function pointsLog()
    {
        return $this->hasMany(PointsLog::class);
    }

    public function getDetailsAttribute()
    {
        return $this->detailsRelation ?? new \App\Models\UserDetails([
            'user_id' => $this->id,
            'birthdate' => null,
            'gender' => null,
            'description' => null,
            'handle' => null,
        ]);
    }

    public function detailsRelation()
    {
        return $this->hasOne(UserDetails::class);
    }

    public function moodEntries()
    {
        return $this->hasMany(MoodEntry::class)->latest();
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class)
            ->withPivot('awarded_at')
            ->withTimestamps();
    }

    public function currentLevel()
    {
        return Level::where('xp_required', '<=', $this->xp)
            ->orderByDesc('level')
            ->first();
    }

}
