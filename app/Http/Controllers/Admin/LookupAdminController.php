<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Localization\Language;
use App\Models\Localization\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LookupAdminController extends Controller
{
    protected array $sections = [
        'goals' => [
            'title' => 'lookup.menu.goals',
            'blocks' => [
                'priority' => [
                    'title' => 'lookup.goals.block.priorities',
                    'table' => 'goal_priorities',
                    'order_by' => 'order',
                    'has_color' => true,
                    'has_icon' => false,
                    'has_image' => false,
                ],
                'status' => [
                    'title' => 'lookup.goals.block.statuses',
                    'table' => 'goal_statuses',
                    'order_by' => 'order',
                    'has_color' => true,
                    'has_icon' => false,
                    'has_image' => false,
                ],
                'type' => [
                    'title' => 'lookup.goals.block.types',
                    'table' => 'goal_types',
                    'order_by' => 'order',
                    'has_color' => false,
                    'has_icon' => true,
                    'has_image' => false,
                ],
            ],
        ],

        'tasks' => [
            'title' => 'lookup.menu.tasks',
            'blocks' => [
                'priority' => [
                    'title' => 'lookup.tasks.block.priorities',
                    'table' => 'task_priorities',
                    'order_by' => 'order',
                    'has_color' => true,
                    'has_icon' => false,
                    'has_image' => false,
                ],
                'status' => [
                    'title' => 'lookup.tasks.block.statuses',
                    'table' => 'task_statuses',
                    'order_by' => 'order',
                    'has_color' => true,
                    'has_icon' => false,
                    'has_image' => false,
                ],
                'type' => [
                    'title' => 'lookup.tasks.block.types',
                    'table' => 'task_types',
                    'order_by' => 'order',
                    'has_color' => false,
                    'has_icon' => true,
                    'has_image' => false,
                ],
            ],
        ],

        'categories' => [
            'title' => 'lookup.menu.categories',
            'blocks' => [
                'category' => [
                    'title' => 'lookup.categories.block.categories',
                    'table' => 'categories',
                    'order_by' => 'order',
                    'has_color' => true,
                    'has_icon' => true,
                    'has_image' => true,
                ],
            ],
        ],
    ];

    protected array $iconList = [
        'heart', 'star', 'sun', 'moon', 'check', 'x',
        'alert-circle', 'smile', 'calendar', 'clock',
        'flame', 'leaf', 'book', 'flag', 'map-pin',
        'target', 'thumbs-up', 'sparkles', 'trophy'
    ];

    public function index(Request $request)
    {
        $section = $request->get('section', 'goals');

        if (!isset($this->sections[$section])) {
            abort(404);
        }

        $blocks = [];
        foreach ($this->sections[$section]['blocks'] as $type => $def) {
            $rows = DB::table($def['table'])
                ->orderBy($def['order_by'] ?? 'id')
                ->get();

            $items = $rows->map(function ($row) use ($section, $type) {
                $slug = Str::slug($row->name, '_');
                $translationKey = "lookup.$section.$type.$slug";

                return [
                    'row' => $row,
                    'slug' => $slug,
                    'full_key' => $translationKey,
                    'label' => t($translationKey),
                ];
            });

            $blocks[] = [
                'type' => $type,
                'title' => t($def['title']),
                'items' => $items,
                'has_color' => $def['has_color'] ?? false,
                'has_icon' => $def['has_icon'] ?? false,
            ];
        }

        app('translation')->flushCache();

        return view('admin.lookups.index', [
            'section' => $section,
            'blocks' => $blocks,
            'sectionsConfig'=> $this->sections,
        ]);
    }

    public function create($locale, string $section, string $type)
    {
        $def = $this->resolveBlock($section, $type);

        $languages = Language::where('is_active', true)->get();

        return view('admin.lookups.create', [
            'section' => $section,
            'type' => $type,
            'title' => t($def['title']),
            'languages' => $languages,
        ]);
    }

    public function store(Request $request, $locale, string $section, string $type)
    {
        $def = $this->resolveBlock($section, $type);

        $request->validate([
            'value' => 'required|array',
        ]);

        $languages = Language::where('is_active', true)->pluck('code');

        $mainLang  = app()->getLocale();
        $mainValue = $request->input("value.$mainLang");

        if (!$mainValue) {
            return back()->withErrors(['value' => 'Main language value is required']);
        }

        $insert = [
            'name'  => $mainValue,
            'order' => (DB::table($def['table'])->max('order') ?? 0) + 1,
        ];

        if (($def['has_color'] ?? false)) {
        $rawHex = $request->input('color_hex');
        $rawPicker = $request->input('color');

        if (!empty($rawHex)) {
            if (!str_starts_with($rawHex, '#')) {
                $rawHex = '#'.$rawHex;
            }
            $update['color'] = $rawHex;
        }
        else {
            $update['color'] = $rawPicker ?: null;
        }
    }

        if ($def['has_icon'] ?? false) {
            $insert['icon'] = $request->input('icon') ?: null;
        }

        $id = DB::table($def['table'])->insertGetId($insert);

        $slug = Str::slug($mainValue, '_');
        $keyWithoutGroup = "$section.$type.$slug";

        foreach ($languages as $code) {
            $val = $request->input("value.$code");

            if ($val) {
                Translation::updateOrCreate(
                    [
                        'group'         => 'lookup',
                        'key'           => $keyWithoutGroup,
                        'language_code' => $code,
                    ],
                    ['value' => $val]
                );
            }
        }

        app('translation')->flushCache();

        return redirect()
            ->route('admin.lookups.index', [
                'locale'  => $locale,
                'section' => $section
            ])
            ->with('success', 'Lookup created.');
    }

    public function edit($locale, string $section, string $type, int $id)
    {
        $def = $this->resolveBlock($section, $type);

        $item = DB::table($def['table'])->find($id);
        if (!$item) {
            abort(404);
        }

        $slug = Str::slug($item->name, '_');
        $keyWithoutGroup = "$section.$type.$slug";
        $fullKey = "lookup.$keyWithoutGroup";

        $languages = Language::where('is_active', true)->get();

        $translations = Translation::where('group', 'lookup')
            ->where('key', $keyWithoutGroup)
            ->whereIn('language_code', $languages->pluck('code'))
            ->get()
            ->keyBy('language_code');

        app('translation')->flushCache();

        return view('admin.lookups.edit', [
            'section' => $section,
            'type' => $type,
            'title' => t($def['title']),
            'item' => $item,
            'slug' => $slug,
            'fullKey' => $fullKey,
            'keyWithoutGroup'=> $keyWithoutGroup,
            'languages' => $languages,
            'translations' => $translations,
            'blockDefinition'=> $def,
            'iconList' => $this->iconList,
        ]);
    }

    public function update(Request $request, $locale, string $section, string $type, int $id)
    {
        $def = $this->resolveBlock($section, $type);

        $item = DB::table($def['table'])->find($id);
        if (!$item) {
            abort(404);
        }

        $slug = Str::slug($item->name, '_');
        $keyWithoutGroup = "$section.$type.$slug";

        $request->validate([
            'value' => 'required|array',
        ]);

        $update = [];

        if (($def['has_color'] ?? false)) {
            $colorHex = $request->input('color_hex') ?: $request->input('color');
            $update['color'] = $colorHex ?: null;
        }

        if (($def['has_icon'] ?? false)) {
            $icon = $request->input('icon');

            if ($icon === '_custom') {
                $icon = $request->input('icon_custom');
            }

            $update['icon'] = $icon ?: null;
        }

        if (($def['has_image'] ?? false) && $request->hasFile('image')) {

            $file = $request->file('image');

            $request->validate([
                'image' => 'image|max:2048',
            ]);

            if (!empty($item->image)) {
                \Storage::disk('public')->delete('categories/'.$item->image);
            }

            $filename = Str::slug($item->name).'_'.time().'.'.$file->getClientOriginalExtension();

            $file->storeAs('categories', $filename, 'public');

            $update['image'] = $filename;
        }


        if (!empty($update)) {
            DB::table($def['table'])->where('id', $id)->update($update);
        }

        $languages = Language::where('is_active', true)->pluck('code');

        foreach ($languages as $code) {
            $val = $request->input("value.$code");

            if ($val === null || $val === '') {
                Translation::where('group', 'lookup')
                    ->where('key', $keyWithoutGroup)
                    ->where('language_code', $code)
                    ->delete();
                continue;
            }

            Translation::updateOrCreate(
                [
                    'group' => 'lookup',
                    'key' => $keyWithoutGroup,
                    'language_code' => $code,
                ],
                [
                    'value' => $val,
                ]
            );
        }

        app('translation')->flushCache();

        return redirect()
            ->route('admin.lookups.index', [
                'locale' => $locale,
                'section' => $section,
            ])
            ->with('success', 'Lookup translations updated.');
    }

    public function destroy($locale, string $section, string $type, int $id)
    {
        $def = $this->resolveBlock($section, $type);

        $item = DB::table($def['table'])->find($id);
        if (!$item) abort(404);

        DB::table($def['table'])->where('id', $id)->delete();

        $slug = Str::slug($item->name, '_');
        $keyWithoutGroup = "$section.$type.$slug";

        Translation::where('group', 'lookup')
            ->where('key', $keyWithoutGroup)
            ->delete();

        app('translation')->flushCache();

        return redirect()
            ->route('admin.lookups.index', ['locale' => $locale, 'section' => $section])
            ->with('success', 'Lookup deleted.');
    }


    protected function resolveBlock(string $section, string $type): array
    {
        if (!isset($this->sections[$section]['blocks'][$type])) {
            abort(404);
        }

        return $this->sections[$section]['blocks'][$type] + [
            'section' => $section,
            'type' => $type,
        ];
    }

    public function reorder(Request $request, $locale, string $section, string $type)
    {
        $def = $this->resolveBlock($section, $type);

        foreach ($request->order as $row) {
            DB::table($def['table'])->where('id', $row['id'])
                ->update(['order' => $row['order']]);
        }

        return response()->json(['success' => true]);
    }
}
