<?php

namespace App\Services;

use App\Models\User;
use App\Models\XpRule;

class XpService
{
    public static function give(User $user, string $ruleKey): void
    {
        $rule = XpRule::where('key', $ruleKey)
            ->where('active', true)
            ->first();

        if (! $rule) {
            return;
        }

        $game = $user->gameDetails;

        if (! $game) {
            return;
        }

        LevelsService::applyXp($game, $rule->xp);
    }
}
