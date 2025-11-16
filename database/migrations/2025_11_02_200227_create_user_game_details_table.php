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
        Schema::create('user_game_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            $table->integer('level')->default(1);
            $table->integer('xp')->default(0);
            $table->integer('xp_next')->default(100);

            $table->integer('coins')->default(0);

            $table->integer('streak_current')->default(0);
            $table->integer('streak_best')->default(0);
            $table->date('last_activity_date')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_game_details');
    }
};
