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
        Schema::table('user_game_details', function (Blueprint $table) {
            $table->unsignedInteger('max_level')
                ->default(1)
                ->after('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_game_details', function (Blueprint $table) {
            $table->dropColumn('max_level');
        });
    }
};
