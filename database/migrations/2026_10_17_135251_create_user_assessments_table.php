<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32);
            $table->string('questionnaire_version', 32);
            $table->string('locale', 10)->default('lt');
            $table->string('scoring_version', 32)->nullable();
            $table->json('profile_scores')->nullable();
            $table->json('dominant_player_types')->nullable();
            $table->json('recommended_preferences')->nullable();
            $table->json('applied_preferences')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type', 'completed_at'], 'user_assessments_history_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_assessments');
    }
};
