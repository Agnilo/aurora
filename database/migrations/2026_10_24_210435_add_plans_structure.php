<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('milestone_id')->nullable()->change();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('goal_id')->nullable()->constrained()->cascadeOnDelete();
            $table->date('scheduled_on')->nullable()->index();
            $table->timestamp('archived_at')->nullable();
        });
        // Preserve task IDs, completion dates and all existing XP records.
        DB::table('tasks')->orderBy('id')->chunkById(200, function ($tasks) {
            foreach ($tasks as $task) {
                $goal = DB::table('milestones')->join('goals', 'goals.id', '=', 'milestones.goal_id')
                    ->where('milestones.id', $task->milestone_id)->select('goals.id', 'goals.user_id')->first();
                if ($goal) DB::table('tasks')->where('id', $task->id)->update([
                    'goal_id' => $goal->id, 'user_id' => $goal->user_id,
                ]);
            }
        });
        Schema::create('habits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('source_task_id')->nullable()->unique()->constrained('tasks')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('frequency', 12); // daily (selected weekdays) or weekly (once per week)
            $table->json('weekdays')->nullable(); // ISO 1..7
            $table->string('timezone')->default('Europe/Vilnius');
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->integer('points')->default(0);
            $table->integer('manual_points')->nullable();
            $table->foreignId('priority_id')->nullable()->constrained('task_priorities')->nullOnDelete();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
        Schema::create('habit_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habit_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->timestamp('completed_at')->nullable();
            $table->integer('awarded_xp');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->unique(['habit_id', 'period_start']);
        });
        Schema::table('points_log', function (Blueprint $table) {
            $table->foreignId('habit_completion_id')->nullable()->unique()
                ->constrained('habit_completions')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Rolling this back after creating standalone tasks would orphan user data.
        throw new \RuntimeException('Restore the pre-upgrade backup to undo the plans migration.');
    }
};
