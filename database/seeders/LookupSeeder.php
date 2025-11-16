<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class LookupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('goal_priorities')->insert([
            ['name' => 'Low', 'color' => '#A5D6A7', 'order' => 1],
            ['name' => 'Medium', 'color' => '#FFF59D', 'order' => 2],
            ['name' => 'High', 'color' => '#EF9A9A', 'order' => 3],
        ]);

        DB::table('goal_statuses')->insert([
            ['name' => 'Planned'],
            ['name' => 'In Progress'],
            ['name' => 'Completed'],
        ]);

        DB::table('goal_types')->insert([
            ['name' => 'Short-term'],
            ['name' => 'Long-term'],
            ['name' => 'Habit'],
        ]);

        DB::table('task_statuses')->insert([
            ['name' => 'Pending'],
            ['name' => 'In Progress'],
            ['name' => 'Completed'],
        ]);

        DB::table('task_types')->insert([
            ['name' => 'One-time'],
            ['name' => 'Daily'],
            ['name' => 'Weekly'],
        ]);

        DB::table('task_priorities')->insert([
            ['name' => 'Low'],
            ['name' => 'Medium'],
            ['name' => 'High'],
        ]);

        DB::table('categories')->insert([
            ['name' => 'Emocijos', 'color' => '#fbbf24'],
            ['name' => 'Santykiai', 'color' => '#f472b6'],
            ['name' => 'Karjera', 'color' => '#60a5fa'],
        ]);
        
    }
}
