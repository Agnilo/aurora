<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Localization\Language;
use App\Models\Localization\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

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
                    'has_image' => false,
                ],
            ],
        ],
    ];

    protected array $iconList = [
        'heart',
        'star',
        'sun',
        'moon',
        'check',
        'x',
        'alert-circle',
        'smile',
        'calendar',
        'clock',
        'flame',
        'leaf',
        'book',
        'flag',
        'map-pin',
        'target',
        'thumbs-up',
        'sparkles',
        'trophy'
    ];

    public function index(Request $request)
    {
        $request->validate(['section' => ['sometimes', 'string', Rule::in(array_keys($this->sections))]]);
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
            'sectionsConfig' => $this->sections,
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

        $this->validateInput($request);

        $languages = Language::where('is_active', true)->pluck('code');

        $mainLang = app()->getLocale();
        $mainValue = $request->input("value.$mainLang");

        if (!$mainValue) {
            return back()->withErrors(['value' => t('lookup.main_value_required')]);
        }

        $insert = [
            'name' => $mainValue,
            'order' => (DB::table($def['table'])->max('order') ?? 0) + 1,
        ];

        if (($def['has_color'] ?? false)) {
            $rawHex = $request->input('color_hex');
            $rawPicker = $request->input('color');

            if (!empty($rawHex)) {
                if (!str_starts_with($rawHex, '#')) {
                    $rawHex = '#' . $rawHex;
                }
                $insert['color'] = $rawHex;
            } else {
                $insert['color'] = $rawPicker ?: null;
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
                        'group' => 'lookup',
                        'key' => $keyWithoutGroup,
                        'language_code' => $code,
                    ],
                    ['value' => $val]
                );
            }
        }

        app('translation')->flushCache();

        return redirect()
            ->route('admin.lookups.index', [
                'locale' => $locale,
                'section' => $section
            ])
            ->with('success', t('lookup.created'));
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
            'keyWithoutGroup' => $keyWithoutGroup,
            'languages' => $languages,
            'translations' => $translations,
            'blockDefinition' => $def,
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

        $this->validateInput($request);

        $update = [];

        if (($def['has_color'] ?? false)) {
            $colorHex = $request->input('color_hex') ?: $request->input('color');
            $update['color'] = $colorHex ? '#' . ltrim($colorHex, '#') : null;
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
                \Storage::disk('public')->delete('categories/' . $item->image);
            }

            $filename = Str::slug($item->name) . '_' . time() . '.' . $file->extension();

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
            ->with('success', t('lookup.updated'));
    }

    public function destroy($locale, string $section, string $type, int $id)
    {
        $def = $this->resolveBlock($section, $type);

        DB::transaction(function () use ($def, $id, $section, $type) {
            $item = DB::table($def['table'])->lockForUpdate()->find($id);
            abort_unless($item, 404);

            if ($def['table'] === 'categories') {
                foreach (['goals', 'tasks', 'habits'] as $table) {
                    if (DB::table($table)->where('category_id', $id)->exists()) {
                        throw ValidationException::withMessages(['category_id' => t('plans.assignment.category_in_use')]);
                    }
                }
            }

            DB::table($def['table'])->where('id', $id)->delete();
            $slug = Str::slug($item->name, '_');

            Translation::where('group', 'lookup')
                ->where('key', "$section.$type.$slug")
                ->delete();
        }, 3);

        app('translation')->flushCache();

        return redirect()
            ->route('admin.lookups.index', ['locale' => $locale, 'section' => $section])
            ->with('success', t('lookup.deleted'));
    }


    private function validateInput(Request $request): void
    {
        $languages = Language::where('is_active', true)->pluck('code')->all();
        $request->validate([
            'value' => ['required', 'array:' . implode(',', $languages)],
            'value.*' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/D'],
            'color_hex' => ['nullable', 'string', 'regex:/^#?[0-9a-fA-F]{6}$/D'],
            'icon' => ['nullable', 'string', 'max:80', 'regex:/^(?:_custom|[a-z][a-z0-9-]*)$/D'],
            'icon_custom' => ['required_if:icon,_custom', 'nullable', 'string', 'max:80', 'regex:/^[a-z][a-z0-9-]*$/D'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048', 'dimensions:max_width=4096,max_height=4096'],
        ]);
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

        $data = $request->validate([
            'order' => ['required', 'array', 'max:1000'],
            'order.*' => ['required', 'array:id,order'],
            'order.*.id' => ['required', 'integer', 'distinct', Rule::exists($def['table'], 'id')],
            'order.*.order' => ['required', 'integer', 'min:0', 'max:65535', 'distinct'],
        ]);
        DB::transaction(function () use ($data, $def) {
            foreach ($data['order'] as $row) {
                DB::table($def['table'])->where('id', $row['id'])->update(['order' => $row['order']]);
            }
        });

        return response()->json(['success' => true]);
    }
}
