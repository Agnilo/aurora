<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function entries(): array
    {
        return [
            ['default', 0, 0],
            ['mint', 25, 10],
            ['lavender', 25, 20],
            ['blue', 25, 30],
            ['peach', 25, 40],
            ['yellow', 15, 50],
            ['pink', 15, 60],
            ['grey', 25, 70],
        ];
    }

    public function up(): void
    {
        foreach ($this->entries() as [$key]) {
            $path = 'images/mascot/backgrounds/' . $key . '.png';
            $size = @getimagesize(public_path($path));
            if (!$size || [$size[0], $size[1], $size[2]] !== [1254, 1254, IMAGETYPE_PNG]) {
                throw new \RuntimeException('Missing or invalid mascot background: ' . $path);
            }
        }
        DB::transaction(function () {
            foreach ($this->entries() as [$key, $price, $order]) {
                DB::table('mascot_items')->insert([
                    'slot' => 'background',
                    'key' => $key,
                    'translation_key' => 'mascot.option.background.' . $key,
                    'layers' => json_encode(['background' => 'images/mascot/backgrounds/' . $key . '.png'], JSON_THROW_ON_ERROR),
                    'color' => null,
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
            $keys = array_column($this->entries(), 0);
            $items = DB::table('mascot_items')->where('slot', 'background')->whereIn('key', $keys);
            if (DB::table('user_mascot_items')->whereIn('mascot_item_id', (clone $items)->pluck('id'))->exists()) {
                throw new \RuntimeException('Cannot roll back purchased mascot backgrounds.');
            }
            DB::table('user_mascots')->select(['id', 'selection'])->chunkById(200, function ($rows) use ($keys) {
                foreach ($rows as $row) {
                    $selection = json_decode($row->selection, true);
                    if (in_array($selection['background'] ?? null, $keys, true)) {
                        throw new \RuntimeException('Cannot roll back saved mascot backgrounds.');
                    }
                }
            });
            $items->delete();
        });
    }
};
