<?php

namespace App\Http\Controllers;

use App\Services\MascotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MascotController extends Controller
{
    public function edit(Request $request, MascotService $mascot)
    {
        $user = $request->user();
        $selection = $mascot->forUser($user);
        $catalog = $mascot->catalog($user);
        $defaults = config('mascot.defaults', []);
        $balance = (int) ($user->gameDetails()->value('coins') ?? 0);
        $ready = $mascot->render($selection)['mode'] === 'layered';
        foreach ($mascot->activeSlots($selection['clothing_mode']) as $slot) {
            $options = $catalog[$slot];
            $ready = $ready && isset($options[$selection[$slot] ?? '']) && $options[$selection[$slot]]['owned'];
        }
        return view('mascot.edit', compact('selection', 'catalog', 'defaults', 'ready', 'balance'));
    }

    public function update(Request $request, MascotService $mascot)
    {
        $catalog = $mascot->catalog($request->user());
        $request->validate(['clothing_mode' => ['required', Rule::in(['separate', 'full'])]]);
        $mode = $request->input('clothing_mode');
        $rules = ['intent' => ['required', Rule::in(['save', 'buy'])], 'quoted_price' => ['nullable', 'integer', 'min:0']];
        foreach ($mascot->activeSlots($mode) as $slot) {
            $options = $catalog[$slot];
            $rules[$slot] = ['required', 'string', Rule::in(array_keys($options))];
        }
        $rules['hair_color'] = ['required', 'string', 'max:16'];
        $data = Validator::make($request->all(), $rules, [
            'required' => t('mascot.invalid'),
            'string' => t('mascot.invalid'),
            'in' => t('mascot.invalid'),
            'integer' => t('mascot.invalid'),
            'min' => t('mascot.invalid'),
        ])->validate();
        $selection = array_intersect_key($data, $catalog) + ['clothing_mode' => $mode, 'hair_color' => $data['hair_color']];
        $buy = $data['intent'] === 'buy';
        $mascot->saveSelection($request->user(), $selection, $buy, isset($data['quoted_price']) ? (int) $data['quoted_price'] : null);
        return redirect()->route('mascot.edit', ['locale' => app()->getLocale()])->with('mascot_saved', true);
    }
}
