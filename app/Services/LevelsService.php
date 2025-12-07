<?php

namespace App\Services;

use App\Models\User;

class LevelsService
{
    public static function checkLevelUp(User $user)
    {
        $nextLevel = $user->level + 1;
        $requiredXP = self::requiredXP($nextLevel);

        if ($user->xp >= $requiredXP) {
            $user->update([
                'level' => $nextLevel
            ]);

        }
    }

    public static function requiredXP($level)
    {
        return $level * 100;
    }
}
