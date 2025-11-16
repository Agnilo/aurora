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
        Schema::create('goals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('priority_id')->nullable()->constrained('goal_priorities')->nullOnDelete();
            $table->foreignId('status_id')->nullable()->constrained('goal_statuses')->nullOnDelete();
            $table->foreignId('type_id')->nullable()->constrained('goal_types')->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->date('deadline')->nullable();

            $table->boolean('is_favorite')->default(false);
            $table->boolean('is_important')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
