<?php

namespace Database\Seeders\Preferences;

use App\Models\Localization\Translation;
use App\Models\Preferences\AssessmentQuestion;
use App\Models\Preferences\UserAssessment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StreakWordingSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $question = AssessmentQuestion::query()
                ->where('questionnaire_type', UserAssessment::TYPE_ONBOARDING)
                ->where('questionnaire_version', OnboardingQuestionSeeder::VERSION)
                ->where('locale', OnboardingQuestionSeeder::LOCALE)
                ->where('code', 'show_streak')
                ->first();

            if ($question) {
                $options = $question->options ?? [];

                foreach ($options as &$option) {
                    if ($option['value'] === 'unsure'
                        && $option['label'] === 'Dar nežinau. Pradžioje serijos nerodyti.') {
                        $option['label'] = 'Dar nežinau. Pradžioje seriją rodyti.';
                    }
                }
                unset($option);

                $question->options = $options;
                $question->save();
            }

            // Update only the original wording; keep admin-edited translations.
            foreach ([
                'lt' => ['Dar nežinau. Pradžioje serijos nerodyti.', 'Dar nežinau. Pradžioje seriją rodyti.'],
                'en' => ['Not sure yet. Hide the streak for now.', 'Not sure yet. Show the streak for now.'],
            ] as $locale => [$old, $new]) {
                Translation::query()
                    ->where('group', 'preferences')
                    ->where('key', 'like', '%show_streak%unsure%')
                    ->where('language_code', $locale)
                    ->where('value', $old)
                    ->update(['value' => $new]);
            }
        });

        Cache::forget('translations.cache');
    }
}
