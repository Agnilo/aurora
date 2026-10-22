<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64);
            $table->string('questionnaire_type', 32);
            $table->string('questionnaire_version', 32);
            $table->string('locale', 10)->default('lt');
            $table->text('text');
            $table->string('answer_type', 32);
            $table->json('options')->nullable();
            $table->string('profile_dimension', 32)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(false);
            $table->timestamps();

            $table->unique(
                ['questionnaire_type', 'questionnaire_version', 'locale', 'code'],
                'assessment_questions_version_code_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_questions');
    }
};
