<?php

namespace App\Http\Controllers\Preferences;

use App\Http\Controllers\Controller;
use App\Models\Preferences\AssessmentQuestion;
use App\Models\Preferences\UserAssessment;
use App\Models\Preferences\UserPreference;
use App\Services\Preferences\OnboardingAssessmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MotivationController extends Controller
{
    public function create(Request $request, string $locale): View
    {
        $preference = UserPreference::firstOrCreate(['user_id' => $request->user()->getKey()]);
        $preference->forceFill([
            'motivation_offer_seen_at' => $preference->motivation_offer_seen_at ?? now(),
            'motivation_remind_at' => $preference->motivation_remind_at ?? now()->addDays(2),
        ])->save();

        $questions = AssessmentQuestion::query()
            ->where('questionnaire_type', UserAssessment::TYPE_ONBOARDING)
            ->where('questionnaire_version', OnboardingAssessmentService::QUESTIONNAIRE_VERSION)
            ->where('locale', OnboardingAssessmentService::SOURCE_LOCALE)
            ->whereNotNull('profile_dimension')
            ->orderBy('sort_order')->orderBy('id')->get();

        return view('preferences.onboarding', [
            'settingsQuestions' => collect(),
            'profileQuestions' => $questions,
            'motivationOnly' => true,
        ]);
    }

    public function store(Request $request, string $locale, OnboardingAssessmentService $service): RedirectResponse
    {
        $data = $request->validate(['answers' => ['required', 'array', 'max:100']]);
        $assessment = $service->completeMotivation($request->user(), $data['answers']);
        $request->user()->preferences()->update(['motivation_remind_at' => null]);

        return redirect()->route('preferences.onboarding.result', ['locale' => $locale, 'assessment' => $assessment->id]);
    }

    public function later(Request $request, string $locale): RedirectResponse
    {
        $preference = UserPreference::firstOrCreate(['user_id' => $request->user()->getKey()]);
        if (!app(\App\Services\Preferences\MotivationProfileService::class)->active($request->user())) {
            $preference->forceFill(['motivation_offer_seen_at' => now(), 'motivation_remind_at' => now()->addDays(2)])->save();
        }
        return redirect()->route('dashboard', ['locale' => $locale]);
    }
}
