<?php

namespace Database\Seeders\Preferences;

use App\Models\Localization\Translation;
use App\Models\Localization\TranslationGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class AdminUserPreferencesTranslationSeeder extends Seeder
{
    public function run(): void
    {
        TranslationGroup::firstOrCreate(['key' => 'admin_user_preferences'], ['name' => 'User preferences']);

        $labels = [
            'title' => ['Naudotojo nuostatos', 'User preferences'],
            'hint' => ['„Pagal apklausą“ palieka ankstesnį naudotojo atsakymą. Administratoriaus pasirinkimas taikomas iš karto.', '“From survey” keeps the user’s survey choice. Admin changes take effect immediately.'],
            'gamification_level' => ['Žaidybos ryškumas', 'Gamification visibility'],
            'gamification.survey' => ['Pagal apklausą', 'From survey'],
            'gamification.hidden' => ['Nerodyti žaidybinių elementų', 'Hide gamification'],
            'gamification.subtle' => ['Santūriai', 'Subtle'],
            'gamification.prominent' => ['Ryškiai', 'Prominent'],
            'xp_mode' => ['Užduočių XP režimas', 'Task XP mode'],
            'xp.survey' => ['Pagal apklausą', 'From survey'],
            'xp.manual' => ['Naudotojas parenka XP', 'User chooses XP'],
            'xp.system' => ['Sistema parenka XP', 'System chooses XP'],
            'show_streak' => ['Dienų serijos rodymas', 'Show daily streak'],
            'streak.survey' => ['Pagal apklausą', 'From survey'],
            'streak.show' => ['Rodyti', 'Show'],
            'streak.hide' => ['Slėpti', 'Hide'],
            'active_assessment' => ['Aktyvi apklausa', 'Active assessment'],
            'no_assessment' => ['Nėra užbaigtos apklausos', 'No completed assessment'],
            'priority_categories' => ['Prioritetinės gyvenimo sritys', 'Priority life areas'],
            'priority.survey' => ['Pagal apklausą', 'From survey'],
            'priority.none' => ['Nė vienos', 'None'],
            'priority.custom' => ['Pasirinkti sritis', 'Choose areas'],
            'select_category' => ['Pasirink bent vieną sritį.', 'Select at least one area.'],
            'assessment_answers' => ['Apklausos atsakymai (tik peržiūra)', 'Survey answers (read only)'],
            'recommendations' => ['Pagal apklausą siūlomos nuostatos', 'Preferences suggested by the survey'],
            'assessment_version' => ['Versija', 'Version'],
            'motivation_profile' => ['Motyvacijos profilis (tik peržiūra)', 'Motivation profile (read only)'],
            'confirmed_at' => ['Nuostatos pakeistos', 'Preferences changed'],
            'onboarding_completed_at' => ['Apklausa užbaigta', 'Onboarding completed'],
            'saved' => ['Nuostatos išsaugotos.', 'Preferences saved.'],
        ];

        foreach ($labels as $key => [$lt, $en]) {
            foreach (['lt' => $lt, 'en' => $en] as $locale => $value) {
                Translation::firstOrCreate(
                    ['group' => 'admin_user_preferences', 'key' => $key, 'language_code' => $locale],
                    ['value' => $value]
                );
            }
        }

        Cache::forget('translations.cache');
    }
}
