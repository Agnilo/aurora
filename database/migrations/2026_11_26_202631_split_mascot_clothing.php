<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $tops = ['default', 'mint', 'rose'];
        $paths = array_merge(array_map(fn($key) => 'images/mascot/tops/' . $key . '.png', [...$tops, 'beige']), ['images/mascot/bottoms/jeans.png', 'images/mascot/bottoms/beige.png']);
        foreach ($paths as $path) {
            $size = @getimagesize(public_path($path));
            if (!$size || [$size[0], $size[1], $size[2]] !== [1254, 1254, IMAGETYPE_PNG]) {
                throw new \RuntimeException('Missing or invalid mascot layer: ' . $path);
            }
        }
        DB::transaction(function () use ($tops) {
            $now = now();
            $converted = [];
            $insert = function (string $slot, string $key, int $order) use ($now) {
                DB::table('mascot_items')->insert([
                    'slot' => $slot,
                    'key' => $key,
                    'translation_key' => 'mascot.option.' . $slot . '.' . $key,
                    'layers' => json_encode([$slot => 'images/mascot/' . ($slot === 'top' ? 'tops' : 'bottoms') . '/' . $key . '.png'], JSON_THROW_ON_ERROR),
                    'color' => null,
                    'price' => 0,
                    'active' => true,
                    'sort_order' => $order,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            };
            foreach ($tops as $i => $key) {
                $item = DB::table('mascot_items')->where('slot', 'outfit')->where('key', $key)->first();
                $layers = $item ? json_decode($item->layers, true) : [];
                if ($item && ($layers['outfit'] ?? null) === 'images/mascot/outfits/' . $key . '.png') {
                    DB::table('mascot_items')->where('id', $item->id)->update([
                        'slot' => 'top',
                        'translation_key' => 'mascot.option.top.' . $key,
                        'layers' => json_encode(['top' => 'images/mascot/tops/' . $key . '.png'], JSON_THROW_ON_ERROR),
                        'updated_at' => $now,
                    ]);
                    $converted[] = $key;
                } elseif ($key === 'default') {
                    $insert('top', 'default', 0);
                }
            }
            $insert('top', 'beige', 100);
            $insert('bottom', 'jeans', 0);
            $insert('bottom', 'beige', 100);
            DB::table('user_mascots')->select(['id', 'selection'])->chunkById(200, function ($rows) use ($converted, $now) {
                foreach ($rows as $row) {
                    $selection = json_decode($row->selection, true, 512, JSON_THROW_ON_ERROR);
                    $old = $selection['outfit'] ?? 'default';
                    $split = in_array($old, $converted, true);
                    $selection['clothing_mode'] = $split ? 'separate' : 'full';
                    $selection['top'] = $split ? $old : 'default';
                    $selection['bottom'] = 'jeans';
                    $selection['outfit'] = $split ? null : $old;
                    DB::table('user_mascots')->where('id', $row->id)->update(['selection' => json_encode($selection, JSON_THROW_ON_ERROR), 'updated_at' => $now]);
                }
            });
        });
    }

    public function down(): void
    {
        throw new \RuntimeException('Clothing conversion preserves purchases by reusing item IDs. Restore the pre-migration database backup to roll back safely.');
    }
};
