<?php

namespace App\Services;

use LogicException;
use App\Models\Level;
use App\Models\UserGameDetail;

class LevelsService
{
    private static function modifiersForRange(int $levelsInRange): array
    {
        if ($levelsInRange <= 1) {
            return [0];
        }

        $modifiers = [];
        $amplitude = 0.25;

        for ($i = 0; $i < $levelsInRange; $i++) {
            $t = $i / ($levelsInRange - 1);

            $curve = 0.5 - 0.5 * cos(pi() * $t);

            $modifier = ($curve - 0.5) * 2 * $amplitude;

            $modifiers[] = $modifier;
        }

        return $modifiers;
    }

    public static function applyXp(UserGameDetail $game, int $xp): void
    {
        self::syncTotalXp($game, (int) $game->xp + $xp);
    }

    public static function syncTotalXp(UserGameDetail $game, int $totalXp): void
    {
        $totalXp = max(0, $totalXp);
        $retainedLevel = max(1, (int) $game->level, (int) $game->max_level);
        $level = 1;
        $nextThreshold = self::xpForNextLevel(1);

        if ($nextThreshold === null) {
            throw new LogicException('XP calculation requires a configured level 1 range.');
        }

        // Move through the original level curve to either the earned level or
        // a newly reached level. Removing XP must never lower the earned level.
        while ($level < $retainedLevel || $totalXp >= $nextThreshold) {
            $nextLevelCost = self::xpForNextLevel($level + 1);

            if ($nextLevelCost === null) {
                // The last configured level has been reached; do not throw a 404.
                break;
            }

            $level++;
            $nextThreshold += $nextLevelCost;
        }

        if ($level < $retainedLevel) {
            // Admin changes to level ranges must not remove earned levels either.
            $level = $retainedLevel;
            $nextThreshold = max($nextThreshold, (int) $game->xp_next);
        }

        $game->xp = $totalXp;
        $game->level = $level;
        $game->max_level = $level;
        $game->xp_next = $nextThreshold;
        $game->save();

        if ($level > $retainedLevel) {
            $user = $game->user;
            $user->setRelation('gameDetails', $game);
            BadgeAwardingService::check($user, 'level');
        }
    }

    public static function xpForNextLevel(int $currentLevel): ?int
    {
        $range = Level::where('level_from', '<=', $currentLevel)
            ->where('level_to', '>=', $currentLevel)
            ->orderBy('level_from')
            ->first();

        if (! $range) {
            return null;
        }

        $levelsInRange = $range->level_to - $range->level_from + 1;
        $baseXp = $range->xp_required / $levelsInRange;

        $index = $currentLevel - $range->level_from;

        $modifiers = self::modifiersForRange($levelsInRange);
        $modifier = $modifiers[$index] ?? 0;

        return max(1, (int) round($baseXp * (1 + $modifier)));
    }
}