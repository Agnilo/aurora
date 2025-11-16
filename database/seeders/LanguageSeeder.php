<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Models\Localization\Language;


class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
        public function run(): void
    {
        Language::updateOrCreate(
            ['code' => 'lt'],
            ['name' => 'Lietuvių', 'is_default' => true, 'is_active' => true]
        );

        Language::updateOrCreate(
            ['code' => 'en'],
            ['name' => 'English', 'is_default' => false, 'is_active' => true]
        );
    }
}
