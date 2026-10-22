<?php

namespace Database\Seeders\Preferences;

use App\Models\Category;
use App\Models\Preferences\AssessmentQuestion;
use App\Models\Preferences\UserAssessment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use LogicException;

class OnboardingQuestionSeeder extends Seeder
{
    public const VERSION = '1.0';

    public const LOCALE = 'lt';

    public function run(): void
    {
        $categoryOptions = Category::query()
            ->orderBy('id')
            ->get(['id', 'name'])
            ->map(fn (Category $category) => [
                'value' => (int) $category->id,
                'label' => $category->name,
            ]);

        if ($categoryOptions->isEmpty()) {
            throw new LogicException(
                'Prieš pildant apklausos klausimus reikia sukurti gyvenimo sritis categories lentelėje.'
            );
        }

        $categoryOptions->push([
            'value' => 'none',
            'label' => 'Kol kas neišskiriu nė vienos srities',
        ]);

        $likertOptions = [
            ['value' => 1, 'label' => 'Visiškai nesutinku'],
            ['value' => 2, 'label' => 'Nesutinku'],
            ['value' => 3, 'label' => 'Iš dalies nesutinku'],
            ['value' => 4, 'label' => 'Nei sutinku, nei nesutinku'],
            ['value' => 5, 'label' => 'Iš dalies sutinku'],
            ['value' => 6, 'label' => 'Sutinku'],
            ['value' => 7, 'label' => 'Visiškai sutinku'],
        ];

        $questions = [
            [
                'code' => 'gamification_level',
                'text' => 'Kiek žaidybinių elementų norėtum matyti?',
                'answer_type' => 'single_choice',
                'is_required' => true,
                'options' => [
                    [
                        'value' => 'hidden',
                        'label' => 'Tik pažangą. Noriu matyti užduotis, tikslus ir jų pažangą, be XP, lygių ir žaidybinių atšventimų ekrane.',
                    ],
                    [
                        'value' => 'subtle',
                        'label' => 'Santūriai. XP ir lygiai gali būti matomi, bet noriu ramaus vaizdo ir nedaug papildomų efektų.',
                    ],
                    [
                        'value' => 'prominent',
                        'label' => 'Ryškiau. Noriu aiškiai matyti XP, lygius, pasiekimus ir užbaigimų atšventimus.',
                    ],
                    [
                        'value' => 'unsure',
                        'label' => 'Dar nežinau. Pradžioje išbandysiu santūrų variantą.',
                    ],
                ],
            ],
            [
                'code' => 'xp_mode',
                'text' => 'Kaip norėtum nustatyti, kiek XP verta užduotis?',
                'answer_type' => 'single_choice',
                'options' => [
                    [
                        'value' => 'system',
                        'label' => 'Pagal bendrą sistemą. Pasirenku, ar užduotis man lengva, vidutinė ar sunki, o XP vertę nustato „Aurora“.',
                    ],
                    [
                        'value' => 'manual',
                        'label' => 'Nustatau pats / pati. Kiekvienai užduočiai pats / pati parenku bazinę XP vertę.',
                    ],
                    [
                        'value' => 'unsure',
                        'label' => 'Dar nežinau. Pradžioje noriu išbandyti bendrą sistemą.',
                    ],
                ],
            ],
            [
                'code' => 'show_streak',
                'text' => 'Kaip norėtum naudoti kasdienio aktyvumo seriją? Serija rodo, kiek dienų iš eilės atlikai bent vieną užduotį.',
                'answer_type' => 'single_choice',
                'options' => [
                    [
                        'value' => 'show',
                        'label' => 'Rodyti. Man patinka matyti ir tęsti dienų seriją.',
                    ],
                    [
                        'value' => 'hide',
                        'label' => 'Nerodyti. Nenoriu, kad dienų serija būtų akcentuojama.',
                    ],
                    [
                        'value' => 'unsure',
                        'label' => 'Dar nežinau. Pradžioje serijos nerodyti.',
                    ],
                ],
            ],
            [
                'code' => 'priority_categories',
                'text' => 'Kurioms gyvenimo sritims šiuo metu nori skirti daugiau dėmesio?',
                'answer_type' => 'multiple_choice',
                'is_required' => true,
                'options' => $categoryOptions->all(),
            ],
        ];

        // Hexad-12: working Lithuanian translation, not a validated Lithuanian scale.
        // Original item IDs and dimensions are retained. Randomize these 12 in the form.
        // Source: Krath et al. (2023), https://doi.org/10.1145/3544548.3580968
        $profileItems = [
            ['hexad_p1', 'philanthropist', 'Mane džiugina galimybė padėti kitiems.'],
            ['hexad_a2', 'achiever', 'Man patinka išmokti gerai atlikti sudėtingas užduotis.'],
            ['hexad_f1', 'free_spirit', 'Man svarbu eiti savo keliu.'],
            ['hexad_r2', 'player', 'Atlygiai mane gerai motyvuoja.'],
            ['hexad_s2', 'socializer', 'Man patinka būti komandos dalimi.'],
            ['hexad_d3', 'disruptor', 'Save laikau maištingu žmogumi.'],
            ['hexad_p4', 'philanthropist', 'Man svarbi kitų gerovė.'],
            ['hexad_a4', 'achiever', 'Man patinka sėkmingai įveikti sudėtingas situacijas.'],
            ['hexad_f3', 'free_spirit', 'Man svarbu būti nepriklausomam / nepriklausomai.'],
            ['hexad_r4', 'player', 'Jei atlygis pakankamas, esu pasirengęs / pasirengusi pasistengti.'],
            ['hexad_s4', 'socializer', 'Man patinka grupinė veikla.'],
            ['hexad_d4', 'disruptor', 'Nemėgstu laikytis taisyklių.'],
        ];

        foreach ($profileItems as [$code, $dimension, $text]) {
            $questions[] = [
                'code' => $code,
                'text' => $text,
                'answer_type' => 'likert',
                'profile_dimension' => $dimension,
                'options' => $likertOptions,
            ];
        }

        DB::transaction(function () use ($questions) {
            foreach ($questions as $index => $question) {
                $code = $question['code'];
                unset($question['code']);

                // Preserve the contents and IDs of an existing questionnaire version.
                // Publish changed wording or options under a new VERSION.
                AssessmentQuestion::firstOrCreate(
                    [
                        'questionnaire_type' => UserAssessment::TYPE_ONBOARDING,
                        'questionnaire_version' => self::VERSION,
                        'locale' => self::LOCALE,
                        'code' => $code,
                    ],
                    array_merge([
                        'profile_dimension' => null,
                        'is_required' => false,
                        'sort_order' => $index + 1,
                    ], $question)
                );
            }
        });
    }
}
