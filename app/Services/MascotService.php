<?php

namespace App\Services;

use App\Models\MascotItem;
use App\Models\User;
use App\Models\UserMascot;
use App\Models\UserMascotItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MascotService
{
    public function forUser(User $user): array
    {
        $selection = UserMascot::where('user_id', $user->getKey())->first()?->selection ?? [];
        $selection = $this->normalize(is_array($selection) ? $selection : []);
        $catalog = $this->catalog($user);
        foreach ($catalog as $slot => $options) {
            if (!($options[$selection[$slot] ?? '']['owned'] ?? false)) {
                $selection[$slot] = config('mascot.defaults.' . $slot);
            }
        }
        $colors = $catalog['hair'][$selection['hair']]['colors'] ?? [];
        if (!isset($colors[$selection['hair_color'] ?? ''])) {
            $selection['hair_color'] = array_key_first($colors);
        }
        if ($selection['clothing_mode'] === 'full' && $selection['outfit'] === null)
            $selection['clothing_mode'] = 'separate';
        return $selection;
    }

    private function items()
    {
        return MascotItem::query()->whereIn('slot', ['hair', 'top', 'bottom', 'outfit', 'shoes', 'body', 'accessory', 'background', 'eye_color', 'expression', 'blush'])->orderBy('sort_order')->orderBy('id')->get();
    }

    private function validItem(MascotItem $item): bool
    {
        $expected = match ($item->slot) {
            'hair' => ['hair-back', 'hair-front'],
            'outfit' => ['outfit'],
            'top' => ['top'],
            'bottom' => ['bottom'],
            'shoes' => ['shoes'],
            'body' => ['body'],
            'accessory' => ['accessory'],
            'background' => ['background'],
            'eye_color' => ['eyes'],
            'expression' => ['expression'],
            'blush' => ['blush'],
            default => [],
        };
        if (!$expected)
            return false;
        $layers = $item->layers;
        if (!is_array($layers) || count($layers) !== count($expected))
            return false;
        foreach ($expected as $layer) {
            if (!$this->validPng($layers[$layer] ?? null))
                return false;
        }
        return true;
    }

    public function normalize(array $selection): array
    {
        $chosen = [];
        $items = $this->items()->filter(fn($item) => $this->validItem($item))->groupBy('slot');
        foreach (config('mascot.defaults', []) as $slot => $default) {
            $key = $selection[$slot] ?? $default;
            if (in_array($slot, ['hair', 'top', 'bottom', 'outfit', 'shoes', 'body', 'accessory', 'background', 'eye_color', 'expression', 'blush'], true)) {
                $valid = is_string($key) && $items->get($slot, collect())->contains('key', $key);
            } else {
                $options = config('mascot.variants.' . $slot, []);
                $valid = is_string($key) && array_key_exists($key, $options) && $this->validVariant($slot, $options[$key]);
            }
            $chosen[$slot] = $valid ? $key : $default;
        }
        $hair = $items->get('hair', collect())->firstWhere('key', $chosen['hair']);
        $colors = $hair ? MascotHairColors::variants($hair, fn($path) => $this->validPng($path)) : [];
        $color = $selection['hair_color'] ?? null;
        $chosen['hair_color'] = is_string($color) && isset($colors[$color]) ? $color : array_key_first($colors);
        $chosen['clothing_mode'] = ($selection['clothing_mode'] ?? 'separate') === 'full' && $chosen['outfit'] !== null ? 'full' : 'separate';
        return $chosen;
    }

    public function catalog(?User $user = null): array
    {
        $catalog = ['hair' => [], 'top' => [], 'bottom' => [], 'outfit' => [], 'shoes' => [], 'body' => [], 'accessory' => [], 'background' => [], 'eye_color' => [], 'expression' => [], 'blush' => []];
        $owned = $user ? UserMascotItem::where('user_id', $user->id)->pluck('mascot_item_id')->map(fn($id) => (int) $id)->all() : [];
        foreach ($this->items() as $item) {
            $isOwned = in_array((int) $item->id, $owned, true);
            if ((!$item->active && !$isOwned) || !$this->validItem($item))
                continue;
            $price = (int) $item->price;
            $catalog[$item->slot][$item->key] = [
                'id' => $item->id,
                'label' => t($item->translation_key),
                'color' => preg_match('/^#[0-9a-f]{6}$/i', $item->color ?? '') ? $item->color : null,
                'layers' => array_map(fn($path) => asset($path), $item->layers),
                'price' => $price,
                'owned' => $price === 0 || $isOwned,
            ];
            if ($item->slot === 'hair') {
                $colors = [];
                foreach (MascotHairColors::variants($item, fn($path) => $this->validPng($path)) as $key => $layers) {
                    $colors[$key] = [
                        'label' => t('mascot.hair_color.' . $key),
                        'color' => MascotHairColors::PALETTE[$key],
                        'layers' => array_map(fn($path) => asset($path), $layers),
                    ];
                }
                $catalog['hair'][$item->key]['colors'] = $colors;
            }
        }
        return $catalog;
    }

    public function activeSlots(string $mode): array
    {
        return $mode === 'full' ? ['hair', 'outfit', 'shoes', 'body', 'accessory', 'background', 'eye_color', 'expression', 'blush'] : ['hair', 'top', 'bottom', 'shoes', 'body', 'accessory', 'background', 'eye_color', 'expression', 'blush'];
    }

    public function saveSelection(User $user, array $selection, bool $buy, ?int $quotedPrice): void
    {
        DB::transaction(function () use ($user, $selection, $buy, $quotedPrice) {
            $game = $user->gameDetails()->lockForUpdate()->firstOrFail();
            MascotItem::query()->where(function ($query) use ($selection) {
                foreach ($selection as $slot => $key) {
                    if (in_array($slot, ['clothing_mode', 'hair_color'], true) || !is_string($key))
                        continue;
                    $query->orWhere(fn($pair) => $pair->where('slot', $slot)->where('key', $key));
                }
            })->orderBy('id')->lockForUpdate()->get();
            $catalog = $this->catalog($user);
            $purchase = [];
            $cost = 0;
            $mode = $selection['clothing_mode'] ?? null;
            if (!in_array($mode, ['full', 'separate'], true))
                throw ValidationException::withMessages(['mascot' => t('mascot.invalid')]);
            $active = $this->activeSlots($mode);
            foreach ($active as $slot) {
                $options = $catalog[$slot];
                $item = $options[$selection[$slot] ?? ''] ?? null;
                if (!$item)
                    throw ValidationException::withMessages(['mascot' => t('mascot.invalid')]);
                if (!$item['owned']) {
                    $purchase[] = $item;
                    $cost += $item['price'];
                }
            }
            $hairColor = $selection['hair_color'] ?? null;
            if (!is_string($hairColor) || !isset($catalog['hair'][$selection['hair']]['colors'][$hairColor])) {
                throw ValidationException::withMessages(['hair_color' => t('mascot.invalid')]);
            }
            $full = array_replace($this->forUser($user), array_intersect_key($selection, array_flip($active)), ['clothing_mode' => $mode, 'hair_color' => $hairColor]);
            if ($this->render($full)['mode'] !== 'layered') {
                throw ValidationException::withMessages(['mascot' => t('mascot.unavailable')]);
            }
            if ($cost > 0) {
                if (!$buy)
                    throw ValidationException::withMessages(['mascot' => t('mascot.locked')]);
                if ($quotedPrice !== $cost)
                    throw ValidationException::withMessages(['mascot' => t('mascot.price_changed')]);
                $changed = $user->gameDetails()->where('coins', '>=', $cost)->decrement('coins', $cost);
                if ($changed !== 1)
                    throw ValidationException::withMessages(['mascot' => t('mascot.insufficient')]);
                foreach ($purchase as $item) {
                    UserMascotItem::create(['user_id' => $user->id, 'mascot_item_id' => $item['id'], 'price_paid' => $item['price']]);
                }
            }
            UserMascot::upsert([
                ['user_id' => $user->id, 'selection' => json_encode($full, JSON_THROW_ON_ERROR)],
            ], ['user_id'], ['selection']);
        }, 3);
        $user->unsetRelation('gameDetails');
    }

    public function render(array $selection = [], ?string $pose = null): array
    {
        $fallback = config('mascot.fallback');
        $poses = config('mascot.poses', []);
        if ($pose !== null && isset($poses[$pose]) && $this->dimensions($poses[$pose])) {
            return ['mode' => 'pose', 'image' => $poses[$pose], 'layers' => []];
        }
        $chosen = $this->normalize($selection);
        $items = $this->items();
        $hair = $items->first(fn($item) => $item->slot === 'hair' && $item->key === $chosen['hair']);
        $shoes = $items->first(fn($item) => $item->slot === 'shoes' && $item->key === $chosen['shoes']);
        $body = $items->first(fn($item) => $item->slot === 'body' && $item->key === $chosen['body']);
        $accessory = $items->first(fn($item) => $item->slot === 'accessory' && $item->key === $chosen['accessory']);
        $fallbackResult = ['mode' => 'fallback', 'image' => $this->dimensions($fallback) ? $fallback : null, 'layers' => []];
        if (!$hair || !$shoes || !$body || !$accessory || !$this->validItem($hair) || !$this->validItem($shoes) || !$this->validItem($body) || !$this->validItem($accessory))
            return $fallbackResult;
        $background = $items->first(fn($item) => $item->slot === 'background' && $item->key === $chosen['background']);
        $hairLayers = MascotHairColors::variants($hair, fn($path) => $this->validPng($path))[$chosen['hair_color']] ?? $hair->layers;
        $layers = [];
        if ($background && $this->validItem($background)) {
            $layers['background'] = $background->layers['background'];
        }
        $layers += [
            'hair-back' => $hairLayers['hair-back'],
            'body' => $body->layers['body'],
            'shoes' => $shoes->layers['shoes'],
        ];
        $slots = $chosen['clothing_mode'] === 'full' ? ['outfit'] : ['bottom', 'top'];
        foreach ($slots as $slot) {
            $item = $items->first(fn($item) => $item->slot === $slot && $item->key === $chosen[$slot]);
            if (!$item || !$this->validItem($item))
                return $fallbackResult;
            $layers[$slot] = $item->layers[$slot];
        }
        foreach (['blush' => 'blush', 'eye_color' => 'eyes', 'expression' => 'expression'] as $slot => $layer) {
            $item = $items->first(fn($item) => $item->slot === $slot && $item->key === $chosen[$slot]);
            if (!$item || !$this->validItem($item))
                return $fallbackResult;
            $layers[$layer] = $item->layers[$layer];
        }
        $layers['hair-front'] = $hairLayers['hair-front'];
        $layers['accessory'] = $accessory->layers['accessory'];
        foreach ($layers as $path) {
            if (!$this->validPng($path))
                return ['mode' => 'fallback', 'image' => $this->dimensions($fallback) ? $fallback : null, 'layers' => []];
        }
        return ['mode' => 'layered', 'image' => null, 'layers' => $layers];
    }

    private function validVariant(string $slot, mixed $variant): bool
    {
        return ($slot === 'accessory' && $variant === null) || $this->validPng($variant);
    }

    private function validPng(mixed $path): bool
    {
        return is_string($path) && preg_match('~^images/mascot/(?:[A-Za-z0-9_-]+/)*[A-Za-z0-9_-]+\.png$~D', $path)
            && $this->dimensions($path) === config('mascot.canvas', [1254, 1254]);
    }

    private function dimensions(?string $path): ?array
    {
        if (!$path || !is_file(public_path($path)))
            return null;
        $size = @getimagesize(public_path($path));
        return $size && $size[2] === IMAGETYPE_PNG ? [$size[0], $size[1]] : null;
    }
}
