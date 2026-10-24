<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Preferences\UserAssessment;
use App\Models\Preferences\UserPreference;
use App\Models\User;
use App\Services\TaskXpService;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserAdminController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function edit($locale, User $user)
    {
        $roles = Role::orderBy('name')->get();
        $user->load('preferences');
        $assessments = $user->assessments()->whereNotNull('completed_at')
            ->whereNotNull('profile_scores')->whereNotNull('scoring_version')
            ->latest('id')->get();
        $activeAssessment = $assessments->firstWhere('id', $user->preferences?->active_assessment_id);
        $activeAssessment?->load('answers.assessmentQuestion');

        return view('admin.users.edit', [
            'user'  => $user,
            'roles' => $roles,
            'categories' => Category::orderBy('order')->orderBy('id')->get(),
            'assessments' => $assessments,
            'activeAssessment' => $activeAssessment,
        ]);
    }

    public function update(Request $request, $locale, User $user)
    {
        $request->validate([
            'name'  => 'required|string',
            'email' => 'required|email',
            'roles' => 'array'
        ]);

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        // Assign roles
        $user->syncRoles($request->roles ?? []);

        return redirect()
            ->route('admin.users.index', $locale)
            ->with('success', t('dashboard.user_updated'));
    }

    public function updatePreferences(Request $request, string $locale, User $user)
    {
        $data = $request->validate([
            'gamification_level' => ['required', Rule::in(['survey', 'hidden', 'subtle', 'prominent'])],
            'xp_mode' => ['required', Rule::in(['survey', 'manual', 'system'])],
            'show_streak' => ['required', Rule::in(['survey', 'show', 'hide'])],
            'priority_mode' => ['required', Rule::in(['survey', 'none', 'custom'])],
            'priority_category_ids' => ['sometimes', 'array'],
            'priority_category_ids.*' => ['required', 'integer', 'distinct', Rule::exists('categories', 'id')],
            'active_assessment_id' => [
                'nullable', 'integer', Rule::exists('user_assessments', 'id')->where(fn ($query) =>
                    $query->where('user_id', $user->id)
                        ->whereNotNull('completed_at')
                        ->whereNotNull('scoring_version')
                        ->whereNotNull('profile_scores')
                ),
            ],
        ]);

        if ($data['priority_mode'] === 'custom' && empty($data['priority_category_ids'])) {
            throw ValidationException::withMessages([
                'priority_category_ids' => t('admin_user_preferences.select_category'),
            ]);
        }

        DB::transaction(function () use ($user, $data) {
            $taskXp = app(TaskXpService::class);
            $previousMode = $taskXp->mode($user);
            $preferences = $user->preferences()->lockForUpdate()->firstOrCreate([]);

            $nextChoice = $data['xp_mode'] === 'survey' ? null : $data['xp_mode'];
            if ($previousMode === UserPreference::XP_MANUAL
                && $nextChoice === UserPreference::XP_SYSTEM) {
                $taskXp->rememberManualPoints($user);
            }

            $preferences->gamification_level = $data['gamification_level'] === 'survey' ? null : $data['gamification_level'];
            $preferences->xp_mode = $nextChoice;
            $preferences->show_streak = match ($data['show_streak']) {
                'show' => true,
                'hide' => false,
                default => null,
            };
            $preferences->priority_category_ids = match ($data['priority_mode']) {
                'survey' => null,
                'none' => [],
                'custom' => array_values(array_map('intval', $data['priority_category_ids'])),
            };
            $preferences->active_assessment_id = $data['active_assessment_id'] ?? null;
            $preferences->confirmed_at = $preferences->gamification_level !== null
                || $preferences->xp_mode !== null
                || $preferences->show_streak !== null
                || $preferences->priority_category_ids !== null
                    ? now() : null;
            $preferences->save();

            $user->unsetRelation('preferences');
            $currentMode = $taskXp->mode($user);

            if ($previousMode === UserPreference::XP_MANUAL
                && $currentMode === UserPreference::XP_SYSTEM
                && $nextChoice !== UserPreference::XP_SYSTEM) {
                $taskXp->rememberManualPoints($user);
            }

            if ($currentMode === UserPreference::XP_SYSTEM) {
                $taskXp->syncOpenTasks($user);
            } elseif ($previousMode === UserPreference::XP_SYSTEM) {
                $taskXp->restoreManualPoints($user);
            }
        });

        return redirect()->route('admin.users.edit', ['locale' => $locale, 'user' => $user->id])
            ->with('success', t('admin_user_preferences.saved'));
    }
}
