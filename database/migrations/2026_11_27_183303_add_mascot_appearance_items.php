<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private function entries(): array
    {
        return [
            ['body', 'default', 'images/mascot/body/default.png', '#fdd9c1', 0, 0],
            ['body', 'warm', 'images/mascot/body/warm.png', '#e0b08b', 0, 10],
            ['body', 'tan', 'images/mascot/body/tan.png', '#b9805c', 0, 20],
            ['body', 'deep', 'images/mascot/body/deep.png', '#7d4e38', 0, 30],
            ['accessory', 'none', 'images/mascot/accessories/none.png', null, 0, 0],
            ['accessory', 'rose-glasses', 'images/mascot/accessories/rose-glasses.png', null, 25, 10],
            ['accessory', 'brown-glasses', 'images/mascot/accessories/brown-glasses.png', null, 25, 20],
        ];
    }

    public function up(): void
    {
        foreach ($this->entries() as [$slot, $key, $path]) {
            $size = @getimagesize(public_path($path));
            if (!$size || [$size[0], $size[1], $size[2]] !== [1254, 1254, IMAGETYPE_PNG]) {
                throw new \RuntimeException('Missing or invalid mascot layer: ' . $path);
            }
        }
        DB::transaction(function () {
            foreach ($this->entries() as [$slot, $key, $path, $color, $price, $order]) {
                if (DB::table('mascot_items')->where('slot', $slot)->where('key', $key)->exists()) {
                    throw new \RuntimeException('Mascot catalog key already exists: ' . $slot . '/' . $key);
                }
                DB::table('mascot_items')->insert([
                    'slot' => $slot,
                    'key' => $key,
                    'translation_key' => 'mascot.option.' . $slot . '.' . $key,
                    'layers' => json_encode([$slot => $path], JSON_THROW_ON_ERROR),
                    'color' => $color,
                    'price' => $price,
                    'active' => true,
                    'sort_order' => $order,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            foreach ($this->entries() as [$slot, $key]) {
                $item = DB::table('mascot_items')->where('slot', $slot)->where('key', $key)->first();
                if ($item && DB::table('user_mascot_items')->where('mascot_item_id', $item->id)->exists()) {
                    throw new \RuntimeException('Restore a backup to roll back purchased mascot items.');
                }
            }
            foreach ($this->entries() as [$slot, $key]) {
                DB::table('mascot_items')->where('slot', $slot)->where('key', $key)->delete();
            }
        });
    }
};
