<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Localization\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function index()
    {
        $languages = Language::orderByDesc('is_default')
            ->orderBy('code')
            ->get();

        return view('admin.languages.index', compact('languages'));
    }

    public function create()
    {
        return view('admin.languages.create');
    }

    public function store(Request $request, $locale)
    {
        $data = $request->validate([
            'code' => 'required|string|max:8|regex:/^[a-z]{2,3}(?:-[A-Za-z]{2})?$/D|unique:languages,code',
            'name' => 'required|string|max:255',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_default'] = !empty($data['is_default']);
        $data['is_active'] = !empty($data['is_active']);

        if ($data['is_default']) {
            Language::query()->update(['is_default' => false]);
        }

        Language::create($data);

        return redirect()
            ->route('admin.languages.index', $locale)
            ->with('success', t('admin.language_created'));
    }

    public function edit($locale, Language $language)
    {
        return view('admin.languages.edit', compact('language'));
    }

    public function update(Request $request, $locale, Language $language)
    {
        $data = $request->validate([
            'code' => 'required|string|max:8|regex:/^[a-z]{2,3}(?:-[A-Za-z]{2})?$/D|unique:languages,code,' . $language->id,
            'name' => 'required|string|max:255',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = !empty($data['is_active']);
        $data['is_default'] = !empty($data['is_default']);


        if ($language->is_default) {
            $data['is_default'] = true;
        }

        if ($data['is_default']) {
            Language::where('id', '!=', $language->id)
                ->update(['is_default' => false]);
        }

        $language->update($data);

        return redirect()
            ->route('admin.languages.index', $locale)
            ->with('success', t('admin.language_updated'));
    }



    public function destroy($locale, Language $language)
    {
        if ($language->is_default) {
            return back()->with('error', t('admin.consistency.cannot_delete_default_language'));
        }

        $language->delete();

        return back()->with('success', t('admin.language_deleted'));
    }
}
