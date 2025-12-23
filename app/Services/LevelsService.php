<?php

namespace App\Services;

use App\Models\User;
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
        
        $game->level ??= 1;
        $game->xp ??= 0;

        if ($game->xp_next === null) {
            $game->xp_next = self::xpForNextLevel($game->level);
        }

        $game->xp = max(0, $game->xp + $xp);

        $leveledUp = false;

        while ($game->xp >= $game->xp_next) {
            $game->level++;
            $leveledUp = true;

            $nextXp = self::xpForNextLevel($game->level);
            if ($nextXp === null) {
                break;
            }

            $game->xp_next += $nextXp;
        }

        $game->save();

        if ($leveledUp) {
            BadgeAwardingService::check($game->user, 'level');
        }
    }

    public static function xpForNextLevel(int $currentLevel): int
    {
        $range = Level::where('level_from', '<=', $currentLevel)
            ->where('level_to', '>=', $currentLevel)
            ->firstOrFail();

        if (! $range) {
            logger()->warning('Missing level range', [
                'level' => $currentLevel,
            ]);

            return null;
        }

        $levelsInRange = $range->level_to - $range->level_from + 1;
        $baseXp = $range->xp_required / $levelsInRange;

        $index = $currentLevel - $range->level_from;

        $modifiers = self::modifiersForRange($levelsInRange);
        $modifier = $modifiers[$index] ?? 0;

        return (int) round($baseXp * (1 + $modifier));
    }
}