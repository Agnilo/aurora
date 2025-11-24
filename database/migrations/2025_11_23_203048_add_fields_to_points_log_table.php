<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('points_log', function (Blueprint $table) {
            // kiek XP buvo pridėta/atimta
            $table->integer('amount')->nullable()->after('points');

            // kokio tipo event (task_completed, task_uncompleted, level_up ir t.t.)
            $table->string('type')->nullable()->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('points_log', function (Blueprint $table) {
            $table->dropColumn('amount');
            $table->dropColumn('type');
        });
    }
};
