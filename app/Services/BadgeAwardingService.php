<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\User;
use App\Models\PointsLog;

class BadgeAwardingService
{
    public static function check(User $user, string $categoryKey): void
    {
        $user->loadMissing('badges', 'gameDetails');

        $badges = Badge::whereHas('category', function ($q) use ($categoryKey) {
            $q->where('key', $categoryKey);
        })->get();

        foreach ($badges as $badge) {
            if ($user->badges->contains($badge->id)) {
                continue;
            }

            if (self::meetsCondition($user, $badge)) {
                self::award($user, $badge);
            }
        }
    }

    private static function meetsCondition(User $user, Badge $badge): bool
    {
        return match ($badge->category->key) {

            'task' =>
                PointsLog::where('user_id', $user->id)
                    ->where('type', 'task_completed')
                    ->count()
                >= $badge->condition['tasks_completed'],

            'milestones' =>
                PointsLog::where('user_id', $user->id)
                    ->where('type', 'milestone_completed')
                    ->count()
                >= $badge->condition['milestones_completed'],

            'goals' =>
                PointsLog::where('user_id', $user->id)
                    ->where('type', 'goal_completed')
                    ->count()
                >= $badge->condition['goals_completed'],

            'streak' =>
                $user->gameDetails->streak_current
                >= $badge->condition['days'],

            'level' =>
                $user->gameDetails->level
                >= $badge->condition['level'],

            default => false,
        };
    }

    private static function award(User $user, Badge $badge): void
    {
        $user->badges()->attach($badge->id, [
            'awarded_at' => now(),
        ]);
    }
}
