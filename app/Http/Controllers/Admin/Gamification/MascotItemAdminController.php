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
    private const SLOTS = ['hair' => ['hair-back', 'hair-front'], 'outfit' => ['outfit']];

    public function index(Request $request)
    {
        $request->validate(['slot' => ['nullable', Rule::in(array_keys(self::SLOTS))]]);
        $items = MascotItem::query()->whereIn('slot', array_keys(self::SLOTS))
            ->when($request->filled('slot'), fn($q) => $q->where('slot', $request->input('slot')))
            ->orderBy('slot')->orderBy('sort_order')->orderBy('id')->paginate(24)->withQueryString();
        return view('admin.gamification.mascot-items.index', compact('items'));
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
        foreach (['body', 'expression', 'accessory'] as $slot) {
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
        ], ['required' => t('mascot.admin.required'), 'max' => t('mascot.admin.invalid_value'), 'file' => t('mascot.admin.invalid_png'), 'mimetypes' => t('mascot.admin.invalid_png'), 'dimensions' => t('mascot.admin.invalid_png'), 'integer' => t('mascot.admin.invalid_value'), 'between' => t('mascot.admin.invalid_value'), 'in' => t('mascot.admin.invalid_value'), 'boolean' => t('mascot.admin.invalid_value')]);
        $slot = $data['slot'];
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
                $isDefault = $record->exists && $record->key === config('mascot.defaults.' . $slot);
                if (($record->exists && $record->active && !$data['active']) || ($isDefault && !$data['active'])) {
                    throw ValidationException::withMessages(['active' => t('mascot.admin.published_locked')]);
                }
                if (($isDefault || ($record->exists && $record->active && $record->price === 0)) && (int) $data['price'] !== 0) {
                    throw ValidationException::withMessages(['price' => t('mascot.admin.free_locked')]);
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
