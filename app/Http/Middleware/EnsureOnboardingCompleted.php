<?php

namespace App\Http\Middleware;

use App\Models\Preferences\UserAssessment;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        // Auth, verification, onboarding, and admin routes remain reachable.
        $protected = $request->routeIs('dashboard', 'goals.*', 'tasks.*', 'category.*',
            'leaderboard.*', 'public.*', 'profile.*', 'users.*');

        if ($user && $protected && ! $user->assessments()
            ->where('type', UserAssessment::TYPE_ONBOARDING)
            ->whereNotNull('completed_at')
            ->whereNotNull('profile_scores')
            ->exists()) {
            $url = route('preferences.onboarding.create', [
                'locale' => $request->route('locale') ?? app()->getLocale(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['onboarding_required' => true, 'redirect' => $url], 409);
            }

            return redirect()->to($url);
        }

        return $next($request);
    }
}
