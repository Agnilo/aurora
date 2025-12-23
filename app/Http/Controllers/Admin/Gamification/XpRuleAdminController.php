<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\XpRule;
use Illuminate\Http\Request;

class XpRuleAdminController extends Controller
{
    public function index()
    {
        return view('admin.gamification.xp-rules.index', [
            'rules' => XpRule::orderBy('key')->get(),
        ]);
    }

    public function edit(XpRule $xpRule)
    {
        return view('admin.gamification.xp-rules.form', [
            'rule' => $xpRule,
        ]);
    }

    public function update(Request $request, XpRule $xpRule)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'xp' => 'required|integer|min:0',
            'active' => 'boolean',
        ]);

        $xpRule->update($validated);

        return redirect()
            ->route('admin.gamification.xp-rules.index', app()->getLocale())
            ->with('success', 'XP taisyklė atnaujinta');
    }
}
