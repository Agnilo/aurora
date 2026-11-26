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
        return $selection;
    }

    private function items()
    {
        return MascotItem::query()->where('active', true)->whereIn('slot', ['hair', 'outfit'])->orderBy('sort_order')->orderBy('id')->get();
    }

    private function validItem(MascotItem $item): bool
    {
        $expected = $item->slot === 'hair' ? ['hair-back', 'hair-front'] : ['outfit'];
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
            if (in_array($slot, ['hair', 'outfit'], true)) {
                $valid = is_string($key) && $items->get($slot, collect())->contains('key', $key);
            } else {
                $options = config('mascot.variants.' . $slot, []);
                $valid = is_string($key) && array_key_exists($key, $options) && $this->validVariant($slot, $options[$key]);
            }
            $chosen[$slot] = $valid ? $key : $default;
        }
        return $chosen;
    }

    public function catalog(?User $user = null): array
    {
        $catalog = ['hair' => [], 'outfit' => []];
        $owned = $user ? UserMascotItem::where('user_id', $user->id)->pluck('mascot_item_id')->map(fn($id) => (int) $id)->all() : [];
        foreach ($this->items() as $item) {
            if (!$this->validItem($item))
                continue;
            $price = (int) $item->price;
            $catalog[$item->slot][$item->key] = [
                'id' => $item->id,
                'label' => t($item->translation_key),
                'color' => preg_match('/^#[0-9a-f]{6}$/i', $item->color ?? '') ? $item->color : null,
                'layers' => array_map(fn($path) => asset($path), $item->layers),
                'price' => $price,
                'owned' => $price === 0 || in_array((int) $item->id, $owned, true),
            ];
        }
        return $catalog;
    }

    public function saveSelection(User $user, array $selection, bool $buy, ?int $quotedPrice): void
    {
        DB::transaction(function () use ($user, $selection, $buy, $quotedPrice) {
            $game = $user->gameDetails()->lockForUpdate()->firstOrFail();
            $catalog = $this->catalog($user);
            $purchase = [];
            $cost = 0;
            foreach ($catalog as $slot => $options) {
                $item = $options[$selection[$slot] ?? ''] ?? null;
                if (!$item)
                    throw ValidationException::withMessages(['mascot' => t('mascot.invalid')]);
                if (!$item['owned']) {
                    $purchase[] = $item;
                    $cost += $item['price'];
                }
            }
            $full = array_replace($this->forUser($user), $selection);
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
        $outfit = $items->first(fn($item) => $item->slot === 'outfit' && $item->key === $chosen['outfit']);
        if (!$hair || !$outfit || !$this->validItem($hair) || !$this->validItem($outfit)) {
            return ['mode' => 'fallback', 'image' => $this->dimensions($fallback) ? $fallback : null, 'layers' => []];
        }
        $layers = [
            'hair-back' => $hair->layers['hair-back'],
            'body' => config('mascot.variants.body.' . $chosen['body']),
            'outfit' => $outfit->layers['outfit'],
            'expression' => config('mascot.variants.expression.' . $chosen['expression']),
            'hair-front' => $hair->layers['hair-front'],
        ];
        $accessory = config('mascot.variants.accessory.' . $chosen['accessory']);
        if ($accessory !== null)
            $layers['accessory'] = $accessory;
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
