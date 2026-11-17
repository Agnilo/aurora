<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            $table->timestamp('motivation_offer_seen_at')->nullable();
            $table->timestamp('motivation_remind_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            $table->dropColumn([
                'motivation_offer_seen_at',
                'motivation_remind_at',
            ]);
        });
    }
};