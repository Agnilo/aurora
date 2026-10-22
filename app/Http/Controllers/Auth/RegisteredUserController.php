<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Preferences\OnboardingAssessmentService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (!$this->hasOnboarding($request)) {
            return redirect()->route('preferences.onboarding.create', ['locale' => app()->getLocale()]);
        }

        return view('auth.register');
    }

    public function store(Request $request, OnboardingAssessmentService $service): RedirectResponse
    {
        if (!$this->hasOnboarding($request)) {
            return redirect()->route('preferences.onboarding.create', ['locale' => app()->getLocale()]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        $answers = $request->session()->get('onboarding.pending.answers');

        try {
            $service->validateAnswers($answers);
        } catch (ValidationException $exception) {
            return redirect()->route('preferences.onboarding.create', ['locale' => app()->getLocale()])
                ->withErrors($exception->errors())->withInput(['answers' => $answers]);
        }

        // Account, responses, and motivation profile succeed or roll back together.
        $user = DB::transaction(function () use ($validated, $answers, $service) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);
            $service->complete($user, $service->start($user), $answers, true);

            return $user;
        });

        // Notifications are dispatched only after the transaction has committed.
        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget('onboarding.pending');

        return redirect()->route('dashboard', ['locale' => app()->getLocale()]);
    }

    private function hasOnboarding(Request $request): bool
    {
        return $request->session()->get('onboarding.pending.version') === OnboardingAssessmentService::QUESTIONNAIRE_VERSION
            && is_array($request->session()->get('onboarding.pending.answers'));
    }
}
