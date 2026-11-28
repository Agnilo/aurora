<?php

namespace App\Services;

use App\Models\MascotItem;

class MascotHairColors
{
    public const PALETTE = [
        'pink' => '#edaca5',
        'brown' => '#a66f4d',
        'blonde' => '#e9c27f',
        'black' => '#35313a',
    ];

    public static function base(MascotItem $item): string
    {
        return isset(self::PALETTE[$item->hair_base_color ?? '']) ? $item->hair_base_color : 'pink';
    }

    public static function variants(MascotItem $item, callable $validPng): array
    {
        $base = self::base($item);
        $variants = [$base => $item->layers];
        foreach ($item->hair_colors ?? [] as $color => $layers) {
            if ($color !== $base && isset(self::PALETTE[$color])) {
                $variants[$color] = $layers;
            }
        }
        return array_filter($variants, static fn($layers) => is_array($layers)
            && count($layers) === 2
            && $validPng($layers['hair-back'] ?? null)
            && $validPng($layers['hair-front'] ?? null));
    }
}
