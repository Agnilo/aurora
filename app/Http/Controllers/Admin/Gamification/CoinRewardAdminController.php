<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Services\CoinRewardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoinRewardAdminController extends Controller
{
    public function edit(string $locale)
    {
        return view('admin.gamification.coin-rewards.edit', [
            'settings' => CoinRewardService::settings(),
            'types' => CoinRewardService::ACTIVITY_TYPES,
        ]);
    }

    public function update(Request $request, string $locale)
    {
        $types = CoinRewardService::ACTIVITY_TYPES;
        $rules = ['rewards' => ['required', 'array:' . implode(',', $types)]];
        $messages = [];
        foreach ($types as $type) {
            $rules['rewards.' . $type] = ['required', 'integer', 'min:0', 'max:10000'];
            foreach (['required', 'integer', 'min', 'max'] as $rule) {
                $messages['rewards.' . $type . '.' . $rule] = t('gamification.coin_rewards.invalid');
            }
        }
        $messages['rewards.required'] = t('gamification.coin_rewards.invalid');
        $messages['rewards.array'] = t('gamification.coin_rewards.invalid');
        $data = $request->validate($rules, $messages);

        DB::transaction(function () use ($data, $types) {
            foreach ($types as $type) {
                DB::table('coin_reward_settings')->where('key', $type)->update([
                    'coins' => (int) $data['rewards'][$type],
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('admin.gamification.coin-rewards.edit', ['locale' => $locale])
            ->with('success', t('gamification.coin_rewards.saved'));
    }
}
