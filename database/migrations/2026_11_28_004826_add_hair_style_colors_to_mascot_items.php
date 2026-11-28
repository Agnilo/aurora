<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mascot_items', function (Blueprint $table) {
            $table->string('hair_base_color', 16)->nullable();
            $table->json('hair_colors')->nullable();
        });

        DB::transaction(function () {
            DB::table('mascot_items')->where('slot', 'hair')->update(['hair_base_color' => 'pink']);
            $base = DB::table('mascot_items')->where('slot', 'hair')->where('key', 'pink')->first();
            if (!$base) {
                return;
            }
            $colors = [];
            foreach (['brown', 'blonde'] as $color) {
                $old = DB::table('mascot_items')->where('slot', 'hair')->where('key', $color)->first();
                if (!$old) {
                    continue;
                }
                $layers = json_decode($old->layers, true, 512, JSON_THROW_ON_ERROR);
                $colors[$color] = $layers;
                DB::table('mascot_items')->where('id', $old->id)->update(['slot' => 'hair-legacy', 'active' => false, 'hair_base_color' => $color]);
            }
            DB::table('mascot_items')->where('id', $base->id)->update([
                'hair_colors' => json_encode($colors, JSON_THROW_ON_ERROR),
                'translation_key' => 'mascot.option.hair.long_wavy',
            ]);
            DB::table('user_mascots')->select(['id', 'selection'])->chunkById(200, function ($rows) use ($colors) {
                foreach ($rows as $row) {
                    $selection = json_decode($row->selection, true);
                    if (!is_array($selection)) {
                        continue;
                    }
                    $old = $selection['hair'] ?? null;
                    if ($old === 'pink' || (is_string($old) && isset($colors[$old]))) {
                        $selection['hair'] = 'pink';
                        $selection['hair_color'] = $old;
                        DB::table('user_mascots')->where('id', $row->id)->update(['selection' => json_encode($selection, JSON_THROW_ON_ERROR)]);
                    }
                }
            });
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Restore the pre-migration database backup to roll back hair-style grouping without losing selections or purchase history.');
    }
};
