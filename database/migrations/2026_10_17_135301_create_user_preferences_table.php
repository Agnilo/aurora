<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('active_assessment_id')->nullable()
                ->constrained('user_assessments')->nullOnDelete();

            // Null means that the user has not confirmed this preference.
            $table->string('xp_mode', 32)->nullable();
            $table->string('gamification_level', 32)->nullable();
            $table->boolean('show_streak')->nullable();
            $table->json('priority_category_ids')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('onboarding_completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
