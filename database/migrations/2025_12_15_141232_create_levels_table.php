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
        Schema::dropIfExists('levels');

        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedInteger('level_from');
            $table->unsignedInteger('level_to')->nullable();
            $table->unsignedInteger('xp_required');
            $table->unsignedInteger('reward_coins')->default(0);
            $table->string('translation_key');

            $table->timestamps();

            $table->unique(['level_from', 'level_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
