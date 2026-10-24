<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Task;
use App\Models\User;

class BadgeAwardingService
{
    public static function check(User $user, string $categoryKey): void
    {
        $user->loadMissing('badges', 'gameDetails');

        $badges = Badge::whereHas('category', function ($q) use ($categoryKey) {
            $q->where('key', $categoryKey);
        })->with('category')->get();

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
        $key = $badge->category->key;
        $conditionKeys = match ($key) {
            'task' => ['tasks_completed', 'task_completed'],
            'milestones' => ['milestones_completed', 'milestone_completed'],
            'goals' => ['goals_completed', 'goal_completed'],
            'streak' => ['days', 'days'],
            'level' => ['level', 'level'],
            default => null,
        };

        if ($conditionKeys === null) {
            return false;
        }

        // Keep valid older conditions readable; a missing/zero threshold grants nothing.
        $required = $badge->condition[$conditionKeys[0]]
            ?? $badge->condition[$conditionKeys[1]] ?? null;
        if (!is_numeric($required) || (int) $required < 1) {
            return false;
        }

        $actual = match ($key) {
            // Count completed task records, not XP entries or completion clicks.
            'task' => Task::whereNotNull('completed_at')
                ->whereHas('milestone.goal', fn ($query) => $query->where('user_id', $user->id))
                ->count(),
            'milestones' => $user->pointsLog()->where('type', 'milestone_completed')->count(),
            'goals' => $user->pointsLog()->where('type', 'goal_completed')->count(),
            'streak' => $user->gameDetails?->streak_current ?? 0,
            'level' => $user->gameDetails?->level ?? 0,
        };

        return $actual >= (int) $required;
    }

    private static function award(User $user, Badge $badge): void
    {
        $user->badges()->syncWithoutDetaching([$badge->id => ['awarded_at' => now()]]);
        $user->unsetRelation('badges');
    }
}
