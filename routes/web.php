<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

use App\Http\Middleware\SetAppLocale;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;

    Route::get('{locale}/lang', function ($locale) {
        $availableLocales = ['en', 'lt'];

        if (!in_array($locale, $availableLocales)) {
            abort(400);
        }

        Session::put('locale', $locale);

        // previous URL
        $previous = url()->previous();
        $parsed = parse_url($previous);
        $segments = collect(explode('/', $parsed['path']))->filter()->values();

        // remove old locale prefix
        if ($segments->count() && in_array($segments[0], $availableLocales)) {
            $segments->shift();
        }

        // build new URL with new locale
        $newPath = '/' . $locale . '/' . $segments->implode('/');

        return redirect($newPath);
    })->name('lang.switch');


    Route::get('/', function () {
        $defaultLocale = config('app.locale');
        return redirect("/{$defaultLocale}");
    });

    // Lokalizuoti visi route'ai su /{locale} prefix
    Route::group([
        'prefix' => '{locale}', 
        'middleware' => [SetAppLocale::class]
    ], function () {

        Route::get('/', function () {
            return view('welcome');
        })->name('home');

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->middleware(['auth', 'verified'])
            ->name('dashboard');

            
        // =========================
        // ADMIN PANEL (only admin)
        // =========================
        Route::middleware(['auth', 'role:admin'])
            ->prefix('admin')
            ->group(function () {

                // Admin dashboard
                Route::get('/', function () {
                    return view('admin.dashboard');
                })->name('admin.dashboard');

                // Translations
                Route::get('/translations', [\App\Http\Controllers\Admin\TranslationAdminController::class, 'index'])
                    ->name('admin.translations.index');

                Route::get('/translations/create', [\App\Http\Controllers\Admin\TranslationAdminController::class, 'create'])
                    ->name('admin.translations.create');

                Route::post('/translations', [\App\Http\Controllers\Admin\TranslationAdminController::class, 'store'])
                    ->name('admin.translations.store');

                Route::get('/translations/edit/{group}/{key}', [\App\Http\Controllers\Admin\TranslationAdminController::class, 'edit'])
                    ->name('admin.translations.edit');

                Route::post('/translations/update/{group}/{key}', [\App\Http\Controllers\Admin\TranslationAdminController::class, 'update'])
                    ->name('admin.translations.update');

                // Upcoming modules (placeholders)
                // Route::get('/languages', ...)->name('admin.languages.index');
                // Route::get('/lookups', ...)->name('admin.lookups.index');
                // Route::get('/users', ...)->name('admin.users.index');
        });

        //CategoryController
        Route::get('/goals/category/{category}', 
            [CategoryController::class, 'show']
        )->name('category.show');

        // Profilio valdymas
        Route::middleware('auth')->group(function () {
            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        });

        // CRUD routes
        Route::resource('users', UserController::class);
        Route::resource('goals', GoalController::class);

        // Laravel Breeze / auth route'ai (login, register, password, etc.)
        require __DIR__.'/auth.php';
    });

