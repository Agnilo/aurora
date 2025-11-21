<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'goal_statuses',
            'goal_types',
            'task_priorities',
            'task_statuses',
            'task_types',
            'categories',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                if (!Schema::hasColumn($t->getTable(), 'order')) {
                    $t->integer('order')->default(0)->after('name');
                }
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'goal_statuses',
            'goal_types',
            'task_priorities',
            'task_statuses',
            'task_types',
            'categories',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                if (Schema::hasColumn($t->getTable(), 'order')) {
                    $t->dropColumn('order');
                }
            });
        }
    }
};
