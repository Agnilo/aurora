<?php

namespace Database\Seeders\Preferences;

use App\Models\Localization\Translation;
use App\Models\Localization\TranslationGroup;
use App\Models\Preferences\AssessmentQuestion;
use App\Models\Preferences\UserAssessment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OnboardingTranslationSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            TranslationGroup::firstOrCreate(
                ['key' => 'preferences'],
                ['name' => 'Preferences']
            );

            foreach (['lt' => 'Nuostatos', 'en' => 'Preferences'] as $locale => $label) {
                $this->store('group.preferences.group', $locale, $label);
            }

            $questions = AssessmentQuestion::query()
                ->where('questionnaire_type', UserAssessment::TYPE_ONBOARDING)
                ->where('questionnaire_version', OnboardingQuestionSeeder::VERSION)
                ->where('locale', OnboardingQuestionSeeder::LOCALE)
                ->get();

            // Base Lithuanian text is retained for fallback and existing question history.
            foreach ($questions as $question) {
                if ($question->text_key) {
                    $this->store($question->text_key, 'lt', $question->text);
                }

                foreach ($question->options ?? [] as $option) {
                    $key = $option['label_key'] ?? null;

                    // Category labels already belong to Aurora's lookup translations.
                    if ($key && str_starts_with($key, 'preferences.')) {
                        $this->store($key, 'lt', $option['label']);
                    }
                }
            }

            foreach ($this->englishMessages() as $key => $value) {
                $this->store(OnboardingQuestionSeeder::TRANSLATION_PREFIX.'.'.$key, 'en', $value);
            }

            foreach ($this->validationMessages() as $locale => $messages) {
                foreach ($messages as $key => $value) {
                    $this->store(OnboardingQuestionSeeder::TRANSLATION_PREFIX.'.errors.'.$key, $locale, $value);
                }
            }
        });

        // Respect the existing t() cache, including when called inside another transaction.
        DB::afterCommit(fn () => Cache::forget('translations.cache'));
    }

    private function store(string $fullKey, string $locale, string $value): void
    {
        [$group, $key] = explode('.', $fullKey, 2);

        // Preserve translations already edited in Aurora's administration interface.
        Translation::firstOrCreate(
            ['group' => $group, 'key' => $key, 'language_code' => $locale],
            ['value' => $value]
        );
    }

    private function englishMessages(): array
    {
        return [
            'questions.gamification_level' => 'How much gamification would you like to see?',
            'questions.xp_mode' => 'How would you like to set how much XP a task is worth?',
            'questions.show_streak' => 'How would you like to use your daily activity streak? A streak shows how many days in a row you have completed at least one task.',
            'questions.priority_categories' => 'Which areas of life would you like to focus on at the moment?',

            'options.gamification_level.hidden' => 'Progress only. I want to see tasks, goals and their progress, without XP, levels or game-like celebrations on screen.',
            'options.gamification_level.subtle' => 'Subtle. XP and levels can be visible, but I prefer a calm interface with few extra effects.',
            'options.gamification_level.prominent' => 'More prominent. I want XP, levels, achievements and completion celebrations to stand out.',
            'options.gamification_level.unsure' => 'Not sure yet. I will start with the subtle option.',

            'options.xp_mode.system' => 'Use the shared system. I choose whether a task feels easy, medium or hard, and Aurora sets its XP value.',
            'options.xp_mode.manual' => 'Set it myself. I choose the base XP value for each task.',
            'options.xp_mode.unsure' => 'Not sure yet. I want to try the shared system first.',

            'options.show_streak.show' => 'Show it. I like seeing and continuing my daily streak.',
            'options.show_streak.hide' => 'Hide it. I do not want the streak to be emphasized.',
            'options.show_streak.unsure' => 'Not sure yet. Hide the streak for now.',
            'options.priority_categories.none' => 'I am not prioritizing any area yet.',

            'likert.1' => 'Strongly disagree',
            'likert.2' => 'Disagree',
            'likert.3' => 'Somewhat disagree',
            'likert.4' => 'Neither agree nor disagree',
            'likert.5' => 'Somewhat agree',
            'likert.6' => 'Agree',
            'likert.7' => 'Strongly agree',

            // Original Hexad-12 items, Krath et al. (2023), DOI: 10.1145/3544548.3580968.
            'questions.hexad_p1' => 'It makes me happy if I am able to help others.',
            'questions.hexad_p4' => 'The well-being of others is important to me.',
            'questions.hexad_s2' => 'I like being part of a team.',
            'questions.hexad_s4' => 'I enjoy group activities.',
            'questions.hexad_f1' => 'It is important to me to follow my own path.',
            'questions.hexad_f3' => 'Being independent is important to me.',
            'questions.hexad_a2' => 'I like mastering difficult tasks.',
            'questions.hexad_a4' => 'I enjoy emerging victorious out of difficult circumstances.',
            'questions.hexad_r2' => 'Rewards are a great way to motivate me.',
            'questions.hexad_r4' => 'If the reward is sufficient, I will put in the effort.',
            'questions.hexad_d3' => 'I see myself as a rebel.',
            'questions.hexad_d4' => 'I dislike following rules.',
        ];
    }

    private function validationMessages(): array
    {
        return [
            'lt' => [
                'unknown_question' => 'Pateiktas klausimas nepriklauso šiai apklausai.',
                'incomplete_profile' => 'Užpildyk visus 12 teiginių arba praleisk motyvacijos dalį.',
                'invalid_profile_value' => 'Pasirink skaičių nuo 1 iki 7.',
                'required_answer' => 'Pasirink atsakymą.',
                'invalid_choice' => 'Pasirink vieną iš pateiktų variantų.',
                'invalid_categories' => 'Pasirink galiojančias gyvenimo sritis.',
                'categories_none_exclusive' => 'Pasirink gyvenimo sritis arba variantą „Kol kas neišskiriu nė vienos srities“.',
                'already_completed' => 'Ši apklausa jau užbaigta. Naujam vertinimui pradėk naują apklausą.',
                'unsupported_assessment' => 'Šis pildymas neatitinka palaikomos klausimyno versijos.',
                'invalid_order' => 'Nepavyko patikrinti klausimų eiliškumo. Atnaujink apklausą ir bandyk dar kartą.',
            ],
            'en' => [
                'unknown_question' => 'The submitted question does not belong to this assessment.',
                'incomplete_profile' => 'Answer all 12 statements or skip the motivation section.',
                'invalid_profile_value' => 'Choose a number from 1 to 7.',
                'required_answer' => 'Choose an answer.',
                'invalid_choice' => 'Choose one of the available options.',
                'invalid_categories' => 'Choose valid areas of life.',
                'categories_none_exclusive' => 'Choose areas of life or the option stating that no area is prioritized yet.',
                'already_completed' => 'This assessment is already complete. Start a new assessment to reassess your preferences.',
                'unsupported_assessment' => 'This assessment does not match the supported questionnaire version.',
                'invalid_order' => 'The question order could not be verified. Reload the assessment and try again.',
            ],
        ];
    }
}
