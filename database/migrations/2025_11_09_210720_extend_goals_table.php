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
        Schema::table('goals', function (Blueprint $table) {
            $table->string('color')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedTinyInteger('progress')->default(0)->nullable();
            $table->boolean('is_completed')->default(false);
            $table->enum('visibility', ['private', 'shared', 'public'])->default('private')->nullable();
            $table->datetime('reminder_date')->nullable();
            $table->json('tags')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            //
        });
    }
};
