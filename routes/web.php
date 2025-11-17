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
        require __DIR__.'/admin.php';
    });

