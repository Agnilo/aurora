<?php

namespace Database\Seeders\Preferences;

use App\Models\Localization\Translation;
use App\Models\Localization\TranslationGroup;
use App\Models\XpRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class TaskXpModeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'low' => [5, 'Lengva užduotis'],
            'medium' => [10, 'Vidutinė užduotis'],
            'high' => [20, 'Sunki užduotis'],
        ] as $level => [$xp, $label]) {
            XpRule::firstOrCreate(['key' => 'task_difficulty_'.$level], [
                'label' => $label,
                'xp' => $xp,
                'active' => true,
            ]);
        }

        TranslationGroup::firstOrCreate(['key' => 'goals'], ['name' => 'Goals']);

        foreach ([
            'form.difficulty' => ['Užduoties sunkumas', 'Task difficulty'],
            'form.difficulty.low' => ['Lengva', 'Easy'],
            'form.difficulty.medium' => ['Vidutinė', 'Medium'],
            'form.difficulty.high' => ['Sunki', 'Hard'],
        ] as $key => [$lt, $en]) {
            foreach (['lt' => $lt, 'en' => $en] as $locale => $value) {
                Translation::firstOrCreate(
                    ['group' => 'goals', 'key' => $key, 'language_code' => $locale],
                    ['value' => $value]
                );
            }
        }

        Cache::forget('translations.cache');
    }
}
