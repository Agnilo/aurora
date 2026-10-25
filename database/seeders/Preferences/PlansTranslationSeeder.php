<?php

namespace Database\Seeders\Preferences;

use App\Models\Localization\{Translation, TranslationGroup};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class PlansTranslationSeeder extends Seeder
{
    public function run(): void
    {
        TranslationGroup::firstOrCreate(['key' => 'plans'], ['name' => 'Plans']);
        $texts = [
            'title' => ['Mano planai', 'My plans'],
            'eyebrow' => ['Tavo kryptis ir veiksmai', 'Your direction and actions'],
            'create' => ['Kurti', 'Create'],
            'choose' => ['Ką norėtum sukurti?', 'What would you like to create?'],
            'close' => ['Uždaryti', 'Close'],
            'goals' => ['Tikslai', 'Goals'],
            'tasks' => ['Užduotys', 'Tasks'],
            'habits' => ['Įpročiai', 'Habits'],
            'goal' => ['Tikslas', 'Goal'],
            'task' => ['Užduotis', 'Task'],
            'habit' => ['Įprotis', 'Habit'],
            'goal_hint' => ['Rezultatas, kurio sieksi per kelis veiksmus. Pavyzdžiui, pasiruošti egzaminui.', 'A result to work towards through several actions. For example, prepare for an exam.'],
            'task_hint' => ['Vienas konkretus veiksmas. Pavyzdžiui, užsiregistruoti į kursus.', 'One specific action. For example, sign up for a course.'],
            'habit_hint' => ['Veiksmas, kurį kartosi. Pavyzdžiui, pasivaikščioti pasirinktomis dienomis.', 'An action to repeat. For example, go for a walk on selected days.'],
            'filter' => ['Filtruoti', 'Filter'],
            'active' => ['Aktyvūs', 'Active'],
            'completed' => ['Atlikta', 'Completed'],
            'archived' => ['Archyvas', 'Archived'],
            'all' => ['Visi', 'All'],
            'empty' => ['Čia dar nėra įrašų. Pradėk nuo „Kurti“.', 'Nothing here yet. Start with Create.'],
            'complete' => ['Pažymėti atlikta', 'Mark complete'],
            'undo' => ['Atšaukti atlikimą', 'Undo completion'],
            'edit_task' => ['Redaguoti užduotį', 'Edit task'],
            'new_task' => ['Nauja užduotis', 'New task'],
            'edit_habit' => ['Redaguoti įprotį', 'Edit habit'],
            'new_habit' => ['Naujas įprotis', 'New habit'],
            'name' => ['Pavadinimas', 'Name'],
            'description' => ['Aprašymas (neprivaloma)', 'Description (optional)'],
            'category' => ['Gyvenimo sritis', 'Life area'],
            'date' => ['Atlikimo data (neprivaloma)', 'Scheduled date (optional)'],
            'optional_goal' => ['Priskirti tikslui (neprivaloma)', 'Link to a goal (optional)'],
            'standalone' => ['Savarankiška užduotis', 'Standalone task'],
            'goal_category_hint' => ['Tikslui priskirta užduotis priklauso to tikslo gyvenimo sričiai.', 'A task linked to a goal uses the goal’s life area.'],
            'select' => ['Pasirink', 'Select'],
            'reward' => ['XP už vieną atlikimą', 'XP per completion'],
            'save' => ['Išsaugoti', 'Save'],
            'saved' => ['Išsaugota.', 'Saved.'],
            'archive' => ['Archyvuoti', 'Archive'],
            'restore' => ['Atkurti', 'Restore'],
            'pause' => ['Sustabdyti įprotį', 'Pause habit'],
            'make_habit' => ['Sukurti įprotį iš šios užduoties', 'Create a habit from this task'],
            'historical_xp' => ['Atliktos užduoties XP išlieka', 'Completed task XP is preserved'],
            'convert_hint' => ['Bus sukurtas įprotis, o sena užduotis perkelta į archyvą. Ankstesni uždirbti užduoties XP išliks.', 'A habit will be created and the old task archived. Previously earned task XP is preserved.'],
            'frequency' => ['Kartojimas', 'Repeat'],
            'daily' => ['Pasirinktomis savaitės dienomis', 'On selected weekdays'],
            'weekly' => ['Vieną kartą per savaitę', 'Once per week'],
            'days' => ['Savaitės dienos', 'Weekdays'],
            'starts' => ['Pradžia', 'Start date'],
            'ends' => ['Pabaiga (neprivaloma)', 'End date (optional)'],
            'timezone' => ['Laiko juosta', 'Time zone'],
            'weekly_hint' => ['Savaitinis įprotis atliekamas vieną kartą nuo pirmadienio iki sekmadienio. Kasdieniam pasirink dienas.', 'A weekly habit is completed once between Monday and Sunday. For a daily habit, select weekdays.'],
            'habit_reward_hint' => ['XP skiriami už vieną dienos arba savaitės atlikimą. Atlygio pakeitimai galioja būsimiems atlikimams.', 'XP is awarded once per day or week. Reward edits apply to future completions.'],
            'schedule_locked' => ['Šis įprotis jau turi istoriją. Kartojimui, pradžiai ar laiko juostai pakeisti sustabdyk jį ir sukurk naują įprotį.', 'This habit has history. To change its recurrence, start date or timezone, pause it and create a new habit.'],
            'history' => ['Atlikimų istorija', 'Completion history'],
            'empty_history' => ['Dar nėra atlikimų.', 'No completions yet.'],
            'undone' => ['Atšaukta', 'Undone'],
            'choose_days' => ['Pasirink bent vieną savaitės dieną.', 'Choose at least one weekday.'],
            'not_due' => ['Šis įprotis šiuo metu nesuplanuotas.', 'This habit is not scheduled for the current period.'],
            'not_today' => ['Šiandien neplanuojama', 'Not scheduled today'],
            'edit_goal' => ['Redaguoti tikslą ir etapus', 'Edit goal and stages'],
            'open_goal' => ['Atverti tikslą', 'Open goal'],
            'goal_setup_hint' => ['Etapai neprivalomi. Išsaugojus tikslą bus galima pridėti užduotis tiesiai prie jo.', 'Stages are optional. After saving, add tasks directly to the goal.'],
            'direct_hint' => ['Čia gali pridėti užduotis be etapų.', 'Add tasks here without creating stages.'],
            'today_habits' => ['Šiandienos įpročiai', 'Today’s habits'],
            'no_habits_today' => ['Šiandien suplanuotų įpročių nėra.', 'No habits scheduled today.'],
            'day_1' => ['Pr', 'Mon'],
            'day_2' => ['An', 'Tue'],
            'day_3' => ['Tr', 'Wed'],
            'day_4' => ['Kt', 'Thu'],
            'day_5' => ['Pn', 'Fri'],
            'day_6' => ['Št', 'Sat'],
            'day_7' => ['Sk', 'Sun'],
        ];
        foreach ($texts as $key => [$lt, $en]) {
            foreach (['lt' => $lt, 'en' => $en] as $locale => $value) {
                Translation::firstOrCreate(['group' => 'plans', 'key' => $key, 'language_code' => $locale], ['value' => $value]);
            }
        }
        Cache::forget('translations.cache');
    }
}
