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
        Schema::table('badges', function (Blueprint $table) {
            $table->foreignId('badge_category_id')
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('icon_path')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->dropForeign(['badge_category_id']);
            $table->dropColumn('badge_category_id');

            $table->dropColumn('icon_path');

            $table->text('description')->nullable(false)->change();
        });
    }
};
