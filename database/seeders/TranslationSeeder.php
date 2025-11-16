<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Models\Localization\Translation;


class TranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Translation::updateOrCreate(
            ['group' => 'dashboard', 'key' => 'you_can', 'language_code' => 'lt'],
            ['value' => 'Tu gali.']
        );

        Translation::updateOrCreate(
            ['group' => 'dashboard', 'key' => 'you_can', 'language_code' => 'en'],
            ['value' => 'You can.']
        );

        // "Tavo tikslai" / "Your goals"
        Translation::updateOrCreate(
            ['group' => 'dashboard', 'key' => 'your_goals', 'language_code' => 'lt'],
            ['value' => 'Tavo tikslai']
        );

        Translation::updateOrCreate(
            ['group' => 'dashboard', 'key' => 'your_goals', 'language_code' => 'en'],
            ['value' => 'Your goals']
        );
    }
}
