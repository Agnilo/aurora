<?php

namespace App\Services;

use LogicException;
use App\Models\Level;
use App\Models\UserGameDetail;
use Illuminate\Support\Facades\DB;

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
        DB::transaction(function () use ($game, $xp) {
            $locked = UserGameDetail::whereKey($game->id)->lockForUpdate()->firstOrFail();
            self::syncTotalXp($locked, (int) $locked->xp + $xp);
            $game->refresh();
        });
    }

    public static function syncTotalXp(UserGameDetail $game, int $totalXp): void
    {
        DB::transaction(function () use ($game, $totalXp) {
            UserGameDetail::whereKey($game->id)->lockForUpdate()->firstOrFail();
            $game->refresh();
            self::syncLockedTotalXp($game, $totalXp);
        });
    }

    private static function syncLockedTotalXp(UserGameDetail $game, int $totalXp): void
    {
        $totalXp = max(0, $totalXp);
        $retainedLevel = max(1, (int) $game->level, (int) $game->max_level);
        $level = 1;
        $nextThreshold = self::xpForNextLevel(1);

        if ($nextThreshold === null) {
            throw new LogicException('XP calculation requires a configured level 1 range.');
        }

        while ($level < $retainedLevel || $totalXp >= $nextThreshold) {
            $nextLevelCost = self::xpForNextLevel($level + 1);
            if ($nextLevelCost === null) {
                break;
            }
            $level++;
            $nextThreshold += $nextLevelCost;
        }

        if ($level < $retainedLevel) {
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
            for ($reached = $retainedLevel + 1; $reached <= $level; $reached++) {
                $range = self::rangeForLevel($reached);
                if ($range) {
                    CoinRewardService::awardOnce($user, 'level', $reached, (int) $range->reward_coins);
                }
            }
            $game->refresh();
            $user->setRelation('gameDetails', $game);
            BadgeAwardingService::check($user, 'level');
        }
    }

    private static function rangeForLevel(int $level): ?Level
    {
        return Level::where('level_from', '<=', $level)
            ->where('level_to', '>=', $level)
            ->orderBy('level_from')
            ->first();
    }

    public static function xpForNextLevel(int $currentLevel): ?int
    {
        $range = self::rangeForLevel($currentLevel);
        if (!$range) {
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
