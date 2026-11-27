<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Localization\Translation;
use App\Models\MascotItem;
use App\Services\MascotAssetUploadService;
use App\Services\MascotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MascotItemAdminController extends Controller
{
    private const SLOTS = ['hair' => ['hair-back', 'hair-front'], 'outfit' => ['outfit'], 'top' => ['top'], 'bottom' => ['bottom'], 'shoes' => ['shoes'], 'body' => ['body'], 'accessory' => ['accessory']];

    public function index(Request $request)
    {
        $request->validate([
            'slot' => ['nullable', Rule::in(array_keys(self::SLOTS))],
            'status' => ['nullable', Rule::in(['published', 'draft'])],
            'per_page' => ['nullable', 'integer', Rule::in([24, 48, 100])],
        ]);
        $items = MascotItem::query()->whereIn('slot', array_keys(self::SLOTS))
            ->when($request->filled('slot'), fn($q) => $q->where('slot', $request->input('slot')))
            ->when($request->filled('status'), fn($q) => $q->where('active', $request->input('status') === 'published'))
            ->orderBy('slot')->orderBy('sort_order')->orderBy('id')->paginate((int) $request->input('per_page', 24))->withQueryString();
        if ($items->currentPage() > $items->lastPage()) {
            return redirect()->route('admin.gamification.mascot-items.index', array_merge(
                $request->only('slot', 'status', 'per_page'),
                ['locale' => app()->getLocale(), 'page' => $items->lastPage()]
            ));
        }
        $usedIds = $this->usedItemIds($items->getCollection());
        return view('admin.gamification.mascot-items.index', compact('items', 'usedIds'));
    }

    public function create(MascotService $mascot)
    {
        return $this->form(new MascotItem(['slot' => 'hair', 'price' => 0, 'active' => false, 'sort_order' => 100]), $mascot);
    }

    public function edit(string $locale, MascotItem $mascot_item, MascotService $mascot)
    {
        abort_unless(isset(self::SLOTS[$mascot_item->slot]), 404);
        return $this->form($mascot_item, $mascot);
    }

    private function form(MascotItem $item, MascotService $mascot)
    {
        $names = [];
        if ($item->exists) {
            [$group, $key] = explode('.', $item->translation_key, 2);
            $names = Translation::where('group', $group)->where('key', $key)->pluck('value', 'language_code')->all();
        }
        $catalog = $mascot->catalog();
        $defaults = config('mascot.defaults');
        $fixed = [];
        foreach (['expression'] as $slot) {
            $path = config('mascot.variants.' . $slot . '.' . $defaults[$slot]);
            if ($path)
                $fixed[$slot] = asset($path);
        }
        $current = array_map(fn($path) => asset($path), $item->layers ?? []);
        $isDefault = $item->exists && $item->key === ($defaults[$item->slot] ?? null);
        return view('admin.gamification.mascot-items.edit', compact('item', 'names', 'catalog', 'defaults', 'fixed', 'current', 'isDefault'));
    }

    public function store(Request $request, MascotAssetUploadService $assets)
    {
        return $this->save($request, new MascotItem(), $assets);
    }

    public function update(Request $request, string $locale, MascotItem $mascot_item, MascotAssetUploadService $assets)
    {
        abort_unless(isset(self::SLOTS[$mascot_item->slot]), 404);
        return $this->save($request, $mascot_item, $assets);
    }

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['required', 'integer', 'distinct', 'exists:mascot_items,id'],
            'action' => ['required', Rule::in(['publish', 'draft', 'price', 'delete'])],
            'price' => ['exclude_unless:action,price', 'required', 'integer', 'between:0,1000000'],
        ], ['required' => t('mascot.admin.required'), 'array' => t('mascot.admin.invalid_value'), 'min' => t('mascot.admin.invalid_value'), 'max' => t('mascot.admin.invalid_value'), 'integer' => t('mascot.admin.invalid_value'), 'distinct' => t('mascot.admin.invalid_value'), 'exists' => t('mascot.admin.invalid_value'), 'in' => t('mascot.admin.invalid_value'), 'between' => t('mascot.admin.invalid_value')]);
        $this->applyAction($data['ids'], $data['action'], isset($data['price']) ? (int) $data['price'] : null);
        return back()->with('success', t('mascot.admin.bulk_done') . ' (' . count($data['ids']) . ')');
    }

    public function destroy(string $locale, MascotItem $mascot_item)
    {
        $this->applyAction([$mascot_item->id], 'delete', null);
        return redirect()->route('admin.gamification.mascot-items.index', ['locale' => $locale])->with('success', t('mascot.admin.bulk_done'));
    }

    private function applyAction(array $ids, string $action, ?int $price): void
    {
        DB::transaction(function () use ($ids, $action, $price) {
            $items = MascotItem::query()->whereIn('id', $ids)->whereIn('slot', array_keys(self::SLOTS))->orderBy('id')->lockForUpdate()->get();
            if ($items->count() !== count($ids)) {
                throw ValidationException::withMessages(['ids' => t('mascot.admin.invalid_value')]);
            }
            foreach ($items as $item) {
                if ($action === 'delete' && $this->isDefault($item)) {
                    throw ValidationException::withMessages(['ids' => t('mascot.admin.default_protected')]);
                }
                if ($action !== 'delete') {
                    $active = match ($action) { 'publish' => true, 'draft' => false, default => (bool) $item->active};
                    $this->validateChange($item, $active, $action === 'price' ? $price : (int) $item->price);
                    if ($action === 'publish' && !$this->hasValidLayers($item)) {
                        throw ValidationException::withMessages(['ids' => t('mascot.admin.invalid_png')]);
                    }
                }
            }
            if ($action === 'delete') {
                if ($this->usedItemIds($items) !== []) {
                    throw ValidationException::withMessages(['ids' => t('mascot.admin.delete_used')]);
                }
                foreach ($items as $item) {
                    $item->delete();
                }
                return;
            }
            if ($action === 'draft' || ($action === 'price' && $price > 0)) {
                $this->grantSavedFreeItems($items->filter(fn($item) => $item->active && (int) $item->price === 0));
            }
            foreach ($items as $item) {
                if ($action === 'price')
                    $item->price = $price;
                else
                    $item->active = $action === 'publish';
                $item->save();
            }
        }, 3);
    }

    private function isDefault(MascotItem $item): bool
    {
        return $item->exists && $item->key === config('mascot.defaults.' . $item->slot);
    }

    private function validateChange(MascotItem $item, bool $active, int $price): void
    {
        if ($this->isDefault($item) && !$active) {
            throw ValidationException::withMessages(['active' => t('mascot.admin.default_protected')]);
        }
        if ($this->isDefault($item) && $price !== 0) {
            throw ValidationException::withMessages(['price' => t('mascot.admin.free_locked')]);
        }
        if ($item->slot === 'body' && $price !== 0) {
            throw ValidationException::withMessages(['price' => t('mascot.admin.skin_free')]);
        }
    }

    private function hasValidLayers(MascotItem $item): bool
    {
        $layers = $item->layers ?? [];
        if (count($layers) !== count(self::SLOTS[$item->slot]))
            return false;
        foreach (self::SLOTS[$item->slot] as $layer) {
            $path = $layers[$layer] ?? null;
            if (!is_string($path) || !preg_match('~^images/mascot/(?:[A-Za-z0-9_-]+/)*[A-Za-z0-9_-]+\.png$~D', $path))
                return false;
            $size = @getimagesize(public_path($path));
            if (!$size || $size[2] !== IMAGETYPE_PNG || [$size[0], $size[1]] !== config('mascot.canvas'))
                return false;
        }
        return true;
    }

    private function usedItemIds($items): array
    {
        if ($items->isEmpty())
            return [];

        $used = array_fill_keys(DB::table('user_mascot_items')
            ->whereIn('mascot_item_id', $items->pluck('id'))
            ->distinct()->pluck('mascot_item_id')->all(), true);
        $bySlot = [];
        foreach ($items as $item) {
            $bySlot[$item->slot][$item->key] = $item->id;
        }
        if (count($used) < $items->count()) {
            DB::table('user_mascots')->select(['id', 'selection'])->chunkById(200, function ($rows) use ($bySlot, $items, &$used) {
                foreach ($rows as $row) {
                    $selection = json_decode($row->selection, true);
                    if (!is_array($selection))
                        continue;
                    foreach ($bySlot as $slot => $keys) {
                        $key = $selection[$slot] ?? null;
                        if (is_string($key) && isset($keys[$key])) {
                            $used[$keys[$key]] = true;
                        }
                    }
                    if (count($used) === $items->count())
                        return false;
                }
            });
        }
        return array_map('intval', array_keys($used));
    }

    private function grantSavedFreeItems($items): void
    {
        if ($items->isEmpty())
            return;
        $now = now();
        DB::table('user_mascots')->select(['id', 'user_id', 'selection'])->chunkById(200, function ($rows) use ($items, $now) {
            foreach ($rows as $row) {
                $selection = json_decode($row->selection, true);
                foreach ($items as $item) {
                    if (($selection[$item->slot] ?? null) !== $item->key)
                        continue;
                    DB::table('user_mascot_items')->insertOrIgnore([
                        'user_id' => $row->user_id,
                        'mascot_item_id' => $item->id,
                        'price_paid' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        });
    }

    private function save(Request $request, MascotItem $item, MascotAssetUploadService $assets)
    {
        $data = $request->validate([
            'slot' => ['required', Rule::in($item->exists ? [$item->slot] : array_keys(self::SLOTS))],
            'name_lt' => ['required', 'string', 'max:120'],
            'name_en' => ['required', 'string', 'max:120'],
            'price' => ['required', 'integer', 'between:0,1000000'],
            'sort_order' => ['required', 'integer', 'between:0,1000000'],
            'active' => ['required', 'boolean'],
            'hair-back' => ['nullable', 'file', 'mimetypes:image/png', 'max:5120', 'dimensions:width=1254,height=1254'],
            'hair-front' => ['nullable', 'file', 'mimetypes:image/png', 'max:5120', 'dimensions:width=1254,height=1254'],
            'outfit' => ['nullable', 'file', 'mimetypes:image/png', 'max:5120', 'dimensions:width=1254,height=1254'],
            'top' => ['nullable', 'file', 'mimetypes:image/png', 'max:5120', 'dimensions:width=1254,height=1254'],
            'bottom' => ['nullable', 'file', 'mimetypes:image/png', 'max:5120', 'dimensions:width=1254,height=1254'],
            'shoes' => ['nullable', 'file', 'mimetypes:image/png', 'max:5120', 'dimensions:width=1254,height=1254'],
            'body' => ['nullable', 'file', 'mimetypes:image/png', 'max:5120', 'dimensions:width=1254,height=1254'],
            'accessory' => ['nullable', 'file', 'mimetypes:image/png', 'max:5120', 'dimensions:width=1254,height=1254'],
        ], ['required' => t('mascot.admin.required'), 'max' => t('mascot.admin.invalid_value'), 'file' => t('mascot.admin.invalid_png'), 'mimetypes' => t('mascot.admin.invalid_png'), 'dimensions' => t('mascot.admin.invalid_png'), 'integer' => t('mascot.admin.invalid_value'), 'between' => t('mascot.admin.invalid_value'), 'in' => t('mascot.admin.invalid_value'), 'boolean' => t('mascot.admin.invalid_value')]);
        $slot = $data['slot'];
        if ($slot === 'body' && (int) $data['price'] !== 0) {
            throw ValidationException::withMessages(['price' => t('mascot.admin.skin_free')]);
        }
        if ($item->exists && $slot === 'accessory' && $item->key === 'none' && $request->hasFile('accessory')) {
            throw ValidationException::withMessages(['accessory' => t('mascot.admin.none_locked')]);
        }
        $existing = $item->layers ?? [];
        foreach (self::SLOTS[$slot] as $layer) {
            if (!$request->hasFile($layer) && (!isset($existing[$layer]) || !is_file(public_path($existing[$layer])))) {
                throw ValidationException::withMessages([$layer => t('mascot.admin.required_png')]);
            }
        }
        $newPaths = [];
        try {
            foreach (self::SLOTS[$slot] as $layer) {
                if ($request->hasFile($layer))
                    $newPaths[$layer] = $assets->store($request->file($layer), $layer);
            }
            DB::transaction(function () use ($item, $data, $slot, $newPaths) {
                $record = $item->exists ? MascotItem::query()->lockForUpdate()->findOrFail($item->id) : $item;
                $record->slot = $slot;
                $this->validateChange($record, (bool) $data['active'], (int) $data['price']);
                if ($record->exists && $record->active && (int) $record->price === 0 && (!$data['active'] || (int) $data['price'] > 0)) {
                    $this->grantSavedFreeItems(collect([$record]));
                }
                $key = $record->exists ? $record->key : 'item-' . Str::uuid();
                $translationKey = $record->exists ? $record->translation_key : 'mascot.option.' . $slot . '.' . $key;
                $layers = array_replace($record->layers ?? [], $newPaths);
                $record->fill([
                    'slot' => $slot,
                    'key' => $key,
                    'translation_key' => $translationKey,
                    'layers' => array_intersect_key($layers, array_flip(self::SLOTS[$slot])),
                    'price' => (int) $data['price'],
                    'sort_order' => (int) $data['sort_order'],
                    'active' => (bool) $data['active']
                ])->save();
                [$group, $nameKey] = explode('.', $translationKey, 2);
                foreach (['lt', 'en'] as $lang) {
                    Translation::updateOrCreate(['group' => $group, 'key' => $nameKey, 'language_code' => $lang], ['value' => $data['name_' . $lang]]);
                }
            });
        } catch (\Throwable $error) {
            foreach ($newPaths as $path)
                File::delete(public_path($path));
            throw $error;
        }
        app('translation')->flushCache();
        return redirect()->route('admin.gamification.mascot-items.index', ['locale' => app()->getLocale()])->with('success', t('mascot.admin.saved'));
    }
}
