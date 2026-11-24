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

    public function store(Request $request, OnboardingAssessmentService $service): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if (!$this->hasOnboarding($request)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => t('security.register_session_expired')], 409);
            }
            return redirect()->route('preferences.onboarding.create', ['locale' => app()->getLocale()]);
        }

        $validated = $request->validate([
            'name' => ['bail', 'required', 'string', 'max:255'],
            'email' => ['bail', 'required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['bail', 'required', 'string', new \App\Rules\PasswordBytes, new \App\Rules\PasswordRequirements],
            'password_confirmation' => ['bail', 'required', 'string', 'same:password'],
        ], [
            'name.required' => t('security.register_name_required'),
            'name.string' => t('security.register_name_invalid'),
            'name.max' => t('security.register_name_long'),
            'email.required' => t('security.register_email_required'),
            'email.string' => t('security.register_email_invalid'),
            'email.email' => t('security.register_email_invalid'),
            'email.lowercase' => t('security.register_email_lowercase'),
            'email.max' => t('security.register_email_long'),
            'email.unique' => t('security.register_email_taken'),
            'password.required' => t('security.register_password_required'),
            'password.string' => t('security.register_password_invalid'),
            'password_confirmation.required' => t('security.register_password_repeat'),
            'password_confirmation.string' => t('security.register_password_invalid'),
            'password_confirmation.same' => t('security.register_password_mismatch'),
        ]);
        $answers = $request->session()->get('onboarding.pending.answers');

        try {
            $service->validateAnswers($answers, false);
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
            $service->complete($user, $service->start($user), $answers, false);

            return $user;
        });

        // Notifications are dispatched only after the transaction has committed.
        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget('onboarding.pending');

        $destination = route('dashboard', ['locale' => app()->getLocale()]);
        return $request->expectsJson()
            ? response()->json(['redirect' => $destination])
            : redirect()->to($destination);
    }

    private function hasOnboarding(Request $request): bool
    {
        return $request->session()->get('onboarding.pending.version') === OnboardingAssessmentService::QUESTIONNAIRE_VERSION
            && is_array($request->session()->get('onboarding.pending.answers'));
    }
}

