<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\User;
use App\Models\UserDetails;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        $game = $user->gameDetails;
        
        $totalPoints = $user->pointsLog()
            ->where(function ($q) {
                $q->where('type', 'task_completed');
            })
            ->sum('amount');

        if (!$game) {
            $game = (object) [
                'level' => 1,
                'xp' => 0,
                'xp_next' => 100,
                'coins' => 0,
                'streak_current' => 0,
                'streak_best' => 0,
                'last_activity_date' => null,
            ];
        }

        $details = $user->details;

        if (!$details) {
            $details = new UserDetails([
                'user_id' => $user->id,
                'birthdate' => null,
                'gender' => null,
                'description' => null,
                'handle' => null,
            ]);
        }

        return view('profile.edit', [
            'user' => $user,
            'game' => $game,
            'totalPoints' => $totalPoints,
            'details' => $details,
            'activeTab' => 'details'
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request, $locale): RedirectResponse
    {
        $user = $request->user();

        $user->name = $request->name;
        if ($request->email) {
            $user->email = $request->email;
        }
        $user->save();

        $details = $user->details ?: new UserDetails();
        $details->user_id = $user->id;

        $details->birthdate = $request->input('birthdate') ?: null;
        $details->gender = $request->input('gender') ?: null;
        $details->description = $request->input('description') ?: null;
        $details->handle = $request->input('handle') ?: null;

        $details->save();

        return redirect()
            ->route('profile.edit', ['locale' => $locale])
            ->with('success', t('profile.updated') ?? 'Profilis atnaujintas.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function passwordForm($locale): View
    {
        $user = auth()->user();
        $game = $user->gameDetails;
        $details = $user->details;

        if (!$game) {
            $game = (object) [
                'level' => 1,
                'xp' => 0,
                'xp_next' => 100,
                'coins' => 0,
                'streak_current' => 0,
                'streak_best' => 0,
                'last_activity_date' => null,
            ];
        }

        $totalPoints = $user->pointsLog()
            ->where(function ($q) {
                $q->where('type', 'task_completed')
                ->orWhere('type', 'milestone_completed')
                ->orWhere('type', 'goal_completed');
            })
            ->sum('amount');

        return view('profile.password', [
            'user' => $user,
            'game' => $game,
            'totalPoints' => $totalPoints,
            'details' => $details,
            'activeTab' => 'password'
        ]);
    }

    public function updatePassword(Request $request, $locale): RedirectResponse
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();
        $user->password = bcrypt($request->password);
        $user->save();

        return redirect()
            ->route('profile.password.form', $locale)
            ->with('success', 'Slaptažodis pakeistas.');
    }

    public function avatar()
    {
        $user = auth()->user();
        $game = $user->gameDetails;
        $details = $user->details;

        if (!$game) {
            $game = (object) [
                'level' => 1,
                'xp' => 0,
                'xp_next' => 100,
                'coins' => 0,
                'streak_current' => 0,
                'streak_best' => 0,
                'last_activity_date' => null,
            ];
        }

        $totalPoints = $user->pointsLog()
            ->where(function ($q) {
                $q->where('type', 'task_completed')
                ->orWhere('type', 'milestone_completed')
                ->orWhere('type', 'goal_completed');
            })
            ->sum('amount');

        return view('profile.avatar',[
            'user' => $user,
            'game' => $game,
            'totalPoints' => $totalPoints,
            'details' => $details,
            'activeTab' => 'avatar'
        ]);
    }

    public function updateAvatar(Request $request, $locale)
    {
        $request->validate([
            'avatar' => 'nullable|image|max:2048'
        ]);

        $user = auth()->user();
        $details = $user->details;

        if ($request->hasFile('avatar')) {

            if ($details->avatar && \Storage::disk('public')->exists($details->avatar)) {
                \Storage::disk('public')->delete($details->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $details->avatar = $path;
        }

        $details->save();

        return back()->with('success', 'Avataras atnaujintas!');
    }

    public function updateCover(Request $request)
    {
        $request->validate([
            'cover' => 'nullable|image|max:4096'
        ]);

        $user = auth()->user();
        $details = $user->details;

        if ($request->hasFile('cover')) {

            if ($details->cover && \Storage::disk('public')->exists($details->cover)) {
                \Storage::disk('public')->delete($details->cover);
            }

            $path = $request->file('cover')->store('covers', 'public');
            $details->cover = $path;
            $details->save();
        }

        return back()->with('success', 'Viršelis atnaujintas!');
    }

    public function badges($locale)
    {
        $user = auth()->user();

        $game = $user->gameDetails;
        $details = $user->details;

        if (!$game) {
            $game = (object) [
                'level' => 1,
                'xp' => 0,
                'xp_next' => 100,
                'coins' => 0,
                'streak_current' => 0,
                'streak_best' => 0,
                'last_activity_date' => null,
            ];
        }

        if (!$details) {
            $details = new \App\Models\UserDetails([
                'user_id' => $user->id,
            ]);
        }

        return view('profile.badges', [
            'user' => $user,
            'game' => $game,
            'details' => $details,
            'badges' => $user->badges()->with('category')->get(),
            'activeTab' => 'badges',
        ]);
    }

}
