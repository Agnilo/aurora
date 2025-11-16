<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LookupSeeder extends Seeder
{
    public function run(): void
    {
        // Goal Priorities
        foreach ([
            ['name' => 'Low', 'color' => '#A5D6A7', 'order' => 1],
            ['name' => 'Medium', 'color' => '#FFF59D', 'order' => 2],
            ['name' => 'High', 'color' => '#EF9A9A', 'order' => 3],
        ] as $p) {
            DB::table('goal_priorities')->updateOrInsert(
                ['name' => $p['name']], // unique key
                ['color' => $p['color'], 'order' => $p['order']]
            );
        }

        // Goal Statuses
        foreach (['Planned', 'In Progress', 'Completed'] as $name) {
            DB::table('goal_statuses')->updateOrInsert(
                ['name' => $name],
                []
            );
        }

        // Goal Types
        foreach (['Short-term', 'Long-term', 'Habit'] as $name) {
            DB::table('goal_types')->updateOrInsert(
                ['name' => $name],
                []
            );
        }

        // Task Statuses
        foreach (['Pending', 'In Progress', 'Completed'] as $name) {
            DB::table('task_statuses')->updateOrInsert(
                ['name' => $name],
                []
            );
        }

        // Task Types
        foreach (['One-time', 'Daily', 'Weekly'] as $name) {
            DB::table('task_types')->updateOrInsert(
                ['name' => $name],
                []
            );
        }

        // Task Priorities
        foreach (['Low', 'Medium', 'High'] as $name) {
            DB::table('task_priorities')->updateOrInsert(
                ['name' => $name],
                []
            );
        }

        // Categories
        foreach ([
            ['name' => 'Emocijos', 'color' => '#fbbf24'],
            ['name' => 'Santykiai', 'color' => '#f472b6'],
            ['name' => 'Karjera', 'color' => '#60a5fa'],
        ] as $cat) {
            DB::table('categories')->updateOrInsert(
                ['name' => $cat['name']],
                ['color' => $cat['color']]
            );
        }
    }
}
