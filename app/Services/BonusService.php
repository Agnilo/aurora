<?php

namespace App\Services;

use App\Models\Bonus;
use App\Models\Task;
use App\Models\User;

class BonusService
{
    public static function applyForContext(User $user, string $contextKey, int|float $baseXp, mixed $subject = null ): int {
        $xp = (float) $baseXp;

        $bonuses = Bonus::query()
            ->where('active', true)
            ->whereHas('bonusContext', fn ($q) =>
                $q->where('key', $contextKey)
            )
            ->orderByRaw("type = 'flat' DESC")
            ->get();

        $bonus = $bonuses->first(fn ($bonus) =>
            self::isBonusActiveForUser($user, $bonus, $subject)
        );

        if (! $bonus) {
            return (int) floor($xp);
        }

        if ($bonus->type === 'flat') {
            $xp += (float) $bonus->value;
        }

        if ($bonus->type === 'multiplier') {
            $xp = (float) floor($xp * (float) $bonus->value);
        }

        return (int) floor($xp);
    }

    protected static function isBonusActiveForUser(User $user, Bonus $bonus, mixed $subject = null): bool
    {
        if ($bonus->bonusContext->key !== 'task_multiplication') {
            return true;
        }

        if (! $subject instanceof Task) {
            return false;
        }

        $priority = strtolower(optional($subject->priority)->name ?? 'default');

        return match (true) {
            str_contains($bonus->key, 'high_priority') => $priority === 'high',
            str_contains($bonus->key, 'medium_priority') => $priority === 'medium',
            str_contains($bonus->key, 'default') => $priority === 'default',
            default => false,
        };
    }
}
