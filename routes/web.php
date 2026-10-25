<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use App\Http\Middleware\SetAppLocale;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\Preferences\OnboardingController;

Route::get('{locale}/lang', function ($locale) {
    $available = ['en', 'lt'];

    if (!in_array($locale, $available)) {
        abort(400);
    }

    Session::put('locale', $locale);

    $previous = url()->previous();
    $parsed   = parse_url($previous);
    $segments = collect(explode('/', $parsed['path']))->filter()->values();

    if ($segments->count() && in_array($segments[0], $available)) {
        $segments->shift();
    }

    $newPath = '/' . $locale . '/' . $segments->implode('/');

    return redirect($newPath);
})->name('lang.switch');

Route::get('/', function () {
    return redirect('/' . config('app.locale'));
});

require __DIR__.'/admin.php';

Route::group([
    'prefix' => '{locale}',
    'middleware' => SetAppLocale::class
], function () {

    Route::get('/', function () {
        return view('welcome');
    })->name('home');

    Route::middleware('auth')->group(function () {
        Route::get('/plans', [\App\Http\Controllers\PlansController::class, 'index'])->name('plans.index');
        Route::resource('tasks', TaskController::class)->only(['create', 'store', 'edit', 'update']);
        Route::post('/tasks/{task}/archive', [TaskController::class, 'archive'])->name('tasks.archive');
        Route::resource('habits', \App\Http\Controllers\HabitController::class)->only(['create', 'store', 'edit', 'update']);
        Route::post('/habits/{habit}/complete', [\App\Http\Controllers\HabitController::class, 'complete'])->name('habits.complete');
        Route::post('/habits/{habit}/archive', [\App\Http\Controllers\HabitController::class, 'archive'])->name('habits.archive');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['auth', 'verified'])
        ->name('dashboard');

    Route::get('/goals/category/{category}', [CategoryController::class, 'show'])
        ->middleware('auth')
        ->name('category.show');

    Route::post('/tasks/{task}/toggle-complete', [TaskController::class, 'toggleComplete'])
        ->middleware('auth')
        ->name('tasks.toggle-complete');

    Route::get('/leaderboard', [LeaderboardController::class, 'index'])
        ->middleware('auth')
        ->name('leaderboard.index');

    Route::get('/public/{user}', [PublicProfileController::class, 'show'])
        ->middleware('auth')
        ->name('public.show');

    Route::middleware('auth')->group(function () {
        Route::prefix('profile')->as('profile.')->group(function () {

            Route::get('/', [ProfileController::class, 'edit'])
                ->name('edit');
            Route::patch('/', [ProfileController::class, 'update'])
                ->name('update');
            Route::delete('/', [ProfileController::class, 'destroy'])
                ->name('destroy');
            Route::get('/password', [ProfileController::class, 'passwordForm'])
                ->name('password.form');
            Route::put('/password', [ProfileController::class, 'updatePassword'])
                ->name('password.update');
            Route::get('/avatar', [ProfileController::class, 'avatar'])
                ->name('avatar');
            Route::post('/avatar', [ProfileController::class, 'updateAvatar'])
                ->name('avatar.update');
            Route::get('/badges', [ProfileController::class, 'badges'])
                ->name('badges');

        });
    });

    Route::resource('users', UserController::class)
        ->middleware(['auth', 'role:admin']);
    Route::resource('goals', GoalController::class)
        ->middleware('auth');

    Route::name('preferences.onboarding.')->group(function () {
        Route::get('/onboarding', [OnboardingController::class, 'create'])
            ->name('create');

        Route::post('/onboarding', [OnboardingController::class, 'store'])
            ->name('store');

        Route::get('/onboarding/result/{assessment}', [OnboardingController::class, 'result'])
            ->middleware('auth')
            ->whereNumber('assessment')
            ->name('result');
    });

    require __DIR__.'/auth.php';
});
