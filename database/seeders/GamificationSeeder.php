<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Level;
use App\Models\XpRule;
use App\Models\Bonus;
use App\Models\Badge;

class GamificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedLevels();
        $this->seedXpRules();
        $this->seedBonuses();
        $this->seedBadges();
    }

    private function seedLevels(): void
    {
        $tiers = [
            [
                'level_from'   => 1,
                'level_to'     => 4,
                'xp_required'  => 0,
                'reward_coins' => 0,
                'translation_key' => 'level.1',
            ],
            [
                'level_from'   => 5,
                'level_to'     => 9,
                'xp_required'  => 100,
                'reward_coins' => 10,
                'translation_key' => 'level.5',
            ],
            [
                'level_from'   => 10,
                'level_to'     => 19,
                'xp_required'  => 500,
                'reward_coins' => 50,
                'translation_key' => 'level.10',
            ],
            [
                'level_from'   => 20,
                'level_to'     => 999,
                'xp_required'  => 2000,
                'reward_coins' => 200,
                'translation_key' => 'level.20',
            ],
        ];

        foreach ($tiers as $tier) {
            Level::updateOrCreate(
                [
                    'level_from' => $tier['level_from'],
                    'level_to'   => $tier['level_to'],
                ],
                $tier
            );
        }
    }

    private function seedXpRules(): void
    {
        $rules = [
            [
                'key' => 'task_complete',
                'label' => 'Užduotis įvykdyta',
                'xp' => 10,
                'active' => true,
            ],
            [
                'key' => 'milestone_complete',
                'label' => 'Etapas įvykdytas',
                'xp' => 30,
                'active' => true,
            ],
            [
                'key' => 'goal_complete',
                'label' => 'Tikslas įvykdytas',
                'xp' => 100,
                'active' => true,
            ],
            [
                'key' => 'daily_streak',
                'label' => 'Dienos serija',
                'xp' => 5,
                'active' => true,
            ],
        ];

        foreach ($rules as $rule) {
            XpRule::updateOrCreate(
                ['key' => $rule['key']],
                $rule
            );
        }
    }

    private function seedBonuses(): void
    {
        $bonuses = [
            [
                'key' => 'streak_7',
                'label' => '7 dienų serija',
                'type' => 'flat',
                'value' => 50,
                'active' => true,
            ],
            [
                'key' => 'focus_boost',
                'label' => 'Fokuso stiprintuvas',
                'type' => 'multiplier',
                'value' => 1.2,
                'active' => true,
            ],
        ];

        foreach ($bonuses as $bonus) {
            Bonus::updateOrCreate(
                ['key' => $bonus['key']],
                $bonus
            );
        }
    }

    private function seedBadges(): void
    {
        $badges = [
            [
                'key' => 'first_goal',
                'name' => 'Pirmasis tikslas',
                'description' => 'Sukūrei pirmą tikslą',
                'icon' => '🎯',
                'condition' => json_encode(['goals_created' => 1]),
            ],
            [
                'key' => 'task_master',
                'name' => 'Užduočių meistras',
                'description' => 'Užbaigei 50 užduočių',
                'icon' => '🔥',
                'condition' => json_encode(['tasks_completed' => 50]),
            ],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(
                ['key' => $badge['key']],
                $badge
            );
        }
    }

}
