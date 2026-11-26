<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coin_reward_settings', function (Blueprint $table) {
            $table->string('key', 64)->primary();
            $table->unsignedInteger('coins');
            $table->timestamps();
        });

        $now = now();
        DB::table('coin_reward_settings')->insert([
            ['key' => 'task_completed', 'coins' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'habit_completed', 'coins' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'milestone_completed', 'coins' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'goal_completed', 'coins' => 20, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('coin_reward_settings');
    }
};
