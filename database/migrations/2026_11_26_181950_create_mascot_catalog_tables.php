<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mascot_items', function (Blueprint $table) {
            $table->id();
            $table->string('slot', 32);
            $table->string('key', 64);
            $table->string('translation_key');
            $table->json('layers');
            $table->string('color', 7)->nullable();
            $table->unsignedInteger('price')->default(0);
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['slot', 'key']);
        });

        Schema::create('user_mascot_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mascot_item_id')->constrained('mascot_items')->restrictOnDelete();
            $table->unsignedInteger('price_paid')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'mascot_item_id']);
        });

        $prices = ['hair' => ['brown' => 30, 'blonde' => 40], 'outfit' => ['mint' => 40, 'rose' => 50]];
        $now = now();
        foreach (['hair', 'outfit'] as $slot) {
            $order = 0;
            foreach (config('mascot.variants.' . $slot, []) as $key => $variant) {
                $layers = $slot === 'hair' ? ['hair-back' => $variant['back'], 'hair-front' => $variant['front']] : [$slot => $variant];
                DB::table('mascot_items')->insert([
                    'slot' => $slot,
                    'key' => $key,
                    'translation_key' => 'mascot.option.' . $slot . '.' . $key,
                    'layers' => json_encode($layers, JSON_THROW_ON_ERROR),
                    'color' => config('mascot.swatches.' . $slot . '.' . $key),
                    'price' => $key === config('mascot.defaults.' . $slot) ? 0 : ($prices[$slot][$key] ?? 0),
                    'active' => true,
                    'sort_order' => $order++,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $paid = DB::table('mascot_items')->where('price', '>', 0)->get();
        DB::table('user_mascots')->select(['id', 'user_id', 'selection'])->chunkById(200, function ($rows) use ($paid, $now) {
            foreach ($rows as $row) {
                $selection = json_decode($row->selection, true);
                foreach ($paid as $item) {
                    if (($selection[$item->slot] ?? null) === $item->key) {
                        DB::table('user_mascot_items')->insertOrIgnore([
                            'user_id' => $row->user_id,
                            'mascot_item_id' => $item->id,
                            'price_paid' => 0,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('user_mascot_items') && DB::table('user_mascot_items')->where('price_paid', '>', 0)->exists()) {
            throw new \RuntimeException('Cannot roll back mascot purchases without restoring spent coins.');
        }
        Schema::dropIfExists('user_mascot_items');
        Schema::dropIfExists('mascot_items');
    }
};
