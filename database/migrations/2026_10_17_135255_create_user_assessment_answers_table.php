<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_assessment_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_question_id')->constrained()->restrictOnDelete();
            $table->json('value');
            $table->unsignedSmallInteger('position')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['user_assessment_id', 'assessment_question_id'],
                'assessment_answers_assessment_question_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_assessment_answers');
    }
};
