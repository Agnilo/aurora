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
            $table->unsignedBigInteger('milestone_id')->nullable()->after('task_id');
            $table->unsignedBigInteger('goal_id')->nullable()->after('milestone_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('points_log', function (Blueprint $table) {
            $table->dropColumn(['milestone_id', 'goal_id']);
        });
    }
};
