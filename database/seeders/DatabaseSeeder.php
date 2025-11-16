<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User 12',
        //     'email' => 'test@example12.com',
        // ]);

        $this->call([
            LookupSeeder::class,
            LanguageSeeder::class,
            TranslationSeeder::class,
            RolePermissionSeeder::class,
        ]);

    }
}
