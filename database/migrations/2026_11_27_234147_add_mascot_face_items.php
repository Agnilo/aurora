<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private function entries(): array
    {
        return [
            ['eye_color', 'brown', 'eyes', 'images/mascot/face/eyes/brown.png', 0, 0, '#79433b'],
            ['eye_color', 'blue', 'eyes', 'images/mascot/face/eyes/blue.png', 20, 10, '#3780b8'],
            ['eye_color', 'green', 'eyes', 'images/mascot/face/eyes/green.png', 20, 20, '#529156'],
            ['eye_color', 'hazel', 'eyes', 'images/mascot/face/eyes/hazel.png', 20, 30, '#aa702b'],
            ['expression', 'neutral', 'expression', 'images/mascot/face/expressions/neutral.png', 0, 0, null],
            ['expression', 'sad', 'expression', 'images/mascot/face/expressions/sad.png', 15, 10, null],
            ['blush', 'peach', 'blush', 'images/mascot/face/blush/peach.png', 0, 0, '#fa927c'],
            ['blush', 'rose', 'blush', 'images/mascot/face/blush/rose.png', 10, 10, '#f5789e'],
            ['blush', 'soft', 'blush', 'images/mascot/face/blush/soft.png', 10, 20, '#f6baa6'],
            ['blush', 'none', 'blush', 'images/mascot/face/blush/none.png', 0, 30, null],
        ];
    }

    public function up(): void
    {
        foreach ($this->entries() as [, , , $path]) {
            $size = @getimagesize(public_path($path));
            if (!$size || [$size[0], $size[1], $size[2]] !== [1254, 1254, IMAGETYPE_PNG]) {
                throw new \RuntimeException('Missing or invalid mascot face layer: ' . $path);
            }
        }
        DB::transaction(function () {
            foreach ($this->entries() as [$slot, $key, $layer, $path, $price, $order, $color]) {
                DB::table('mascot_items')->insert([
                    'slot' => $slot,
                    'key' => $key,
                    'translation_key' => 'mascot.option.' . $slot . '.' . $key,
                    'layers' => json_encode([$layer => $path], JSON_THROW_ON_ERROR),
                    'color' => $color,
                    'price' => $price,
                    'sort_order' => $order,
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            $slots = ['eye_color', 'expression', 'blush'];
            $items = DB::table('mascot_items')->whereIn('slot', $slots);
            if (DB::table('user_mascot_items')->whereIn('mascot_item_id', (clone $items)->pluck('id'))->exists()) {
                throw new \RuntimeException('Cannot roll back purchased mascot face items.');
            }
            $expected = [];
            foreach ($this->entries() as [$slot, $key])
                $expected[$slot][] = $key;
            foreach ((clone $items)->get() as $item) {
                if (!in_array($item->key, $expected[$item->slot] ?? [], true)) {
                    throw new \RuntimeException('Cannot roll back additional mascot face items.');
                }
            }
            DB::table('user_mascots')->select(['id', 'selection'])->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $selection = json_decode($row->selection, true);
                    if (isset($selection['eye_color']) || isset($selection['blush']) || (isset($selection['expression']) && $selection['expression'] !== 'neutral')) {
                        throw new \RuntimeException('Cannot roll back saved mascot face selections.');
                    }
                }
            });
            $items->delete();
        });
    }
};
