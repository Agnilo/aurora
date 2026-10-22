<?php

namespace App\Http\Controllers\Preferences;

use App\Http\Controllers\Controller;
use App\Models\Preferences\AssessmentQuestion;
use App\Models\Preferences\UserAssessment;
use App\Services\Preferences\OnboardingAssessmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function create(Request $request, string $locale): View
    {
        $questions = AssessmentQuestion::query()
            ->where('questionnaire_type', UserAssessment::TYPE_ONBOARDING)
            ->where('questionnaire_version', OnboardingAssessmentService::QUESTIONNAIRE_VERSION)
            ->where('locale', OnboardingAssessmentService::SOURCE_LOCALE)
            ->orderBy('sort_order')->orderBy('id')->get();

        return view('preferences.onboarding', [
            'settingsQuestions' => $questions->whereNull('profile_dimension'),
            'profileQuestions' => $questions->whereNotNull('profile_dimension'),
        ]);
    }

    public function store(Request $request, string $locale, OnboardingAssessmentService $service): RedirectResponse
    {
        $validated = $request->validate([
            'answers' => ['required', 'array'],
            'include_profile' => ['required', 'accepted'],
        ]);
        $service->validateAnswers($validated['answers']);

        if (!$request->user()) {
            $request->session()->put('onboarding.pending', [
                'answers' => $validated['answers'],
                'version' => OnboardingAssessmentService::QUESTIONNAIRE_VERSION,
                'locale' => app()->getLocale(),
            ]);

            return redirect()->route('register', ['locale' => $locale]);
        }

        // One short transaction on final submit. No DB draft is created on page load.
        $assessment = DB::transaction(function () use ($request, $validated, $service) {
            $user = $request->user();
            User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            // Repeated final submissions reuse the existing completed onboarding.
            $completed = $user->assessments()
                ->where('type', UserAssessment::TYPE_ONBOARDING)
                ->where('questionnaire_version', OnboardingAssessmentService::QUESTIONNAIRE_VERSION)
                ->whereNotNull('completed_at')
                ->whereNotNull('profile_scores')
                ->latest('id')->first();

            if ($completed) {
                return $completed;
            }

            return $service->complete(
                $user,
                $service->start($user),
                $validated['answers'],
                true
            );
        });

        return redirect()->route('preferences.onboarding.result', [
            'locale' => $locale,
            'assessment' => $assessment->id,
        ]);
    }

    public function result(Request $request, string $locale, int $assessment): View
    {
        $record = $request->user()->assessments()
            ->whereNotNull('completed_at')->findOrFail($assessment);

        return view('preferences.result', ['assessment' => $record]);
    }
}
