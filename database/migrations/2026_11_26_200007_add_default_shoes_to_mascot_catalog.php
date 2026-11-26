<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('mascot_items')->insert([
            'slot' => 'shoes',
            'key' => 'default',
            'translation_key' => 'mascot.option.shoes.default',
            'layers' => json_encode(['shoes' => 'images/mascot/shoes/default.png'], JSON_THROW_ON_ERROR),
            'color' => '#f3ebe0',
            'price' => 0,
            'active' => true,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        $ids = DB::table('mascot_items')->where('slot', 'shoes')->pluck('id');
        if (
            DB::table('mascot_items')->where('slot', 'shoes')->where('key', '!=', 'default')->exists()
            || DB::table('user_mascot_items')->whereIn('mascot_item_id', $ids)->exists()
        ) {
            throw new \RuntimeException('Cannot roll back shoes after additional shoe items or purchases exist.');
        }
        DB::table('mascot_items')->where('slot', 'shoes')->where('key', 'default')->delete();
    }
};
