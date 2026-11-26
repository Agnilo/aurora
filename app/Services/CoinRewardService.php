<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserCoinAward;
use Illuminate\Support\Facades\DB;

class CoinRewardService
{
    public const ACTIVITY_TYPES = ['task_completed', 'habit_completed', 'milestone_completed', 'goal_completed'];

    public static function settings(): array
    {
        return DB::table('coin_reward_settings')->pluck('coins', 'key')->map(fn($value) => (int) $value)->all();
    }

    public static function balanceForUpdate(User $user): int
    {
        return (int) $user->gameDetails()->lockForUpdate()->firstOrCreate([])->coins;
    }

    public static function awardActivity(User $user, string $type, int $id): bool
    {
        if (!in_array($type, self::ACTIVITY_TYPES, true)) {
            throw new \InvalidArgumentException('Unknown coin reward type.');
        }

        $amount = DB::table('coin_reward_settings')->where('key', $type)->value('coins');
        if ($amount === null) {
            throw new \LogicException('Missing coin reward setting: ' . $type);
        }

        return self::awardOnce($user, $type, $id, (int) $amount);
    }

    public static function awardOnce(User $user, string $type, int $id, int $amount): bool
    {
        if ($amount < 0 || $id < 1) {
            throw new \InvalidArgumentException('Invalid coin award.');
        }

        return DB::transaction(function () use ($user, $type, $id, $amount) {
            $game = $user->gameDetails()->lockForUpdate()->firstOrCreate([]);
            $award = UserCoinAward::firstOrCreate([
                'user_id' => $user->id,
                'award_type' => $type,
                'awardable_id' => $id,
            ], [
                'coins' => $amount,
                'awarded_at' => now(),
            ]);

            if (!$award->wasRecentlyCreated) {
                return false;
            }

            if ($amount > 0) {
                $game->increment('coins', $amount);
            }
            $user->unsetRelation('gameDetails');

            return true;
        }, 3);
    }
}
