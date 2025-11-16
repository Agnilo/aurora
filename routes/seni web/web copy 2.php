<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GoalController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Http\Middleware\SetAppLocale;

Route::get('/', function () {
    $defaultLocale = config('app.locale');
    return redirect("/{$defaultLocale}");
});

// Lokalizuoti visi route'ai su /{locale} prefix
Route::group(['prefix' => '{locale}', 'middleware' => [SetAppLocale::class]], function () {

    Route::get('/', function () {
        return view('welcome');
    })->name('home');

    // ✅ Tikras dashboard view (be redirect į save)
    Route::get('/dashboard', function () {
        return view('dashboard'); // arba [DashboardController::class, 'index']
    })->middleware(['auth', 'verified'])->name('dashboard');

    // ✅ Profilio valdymas
    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    // ✅ CRUD routes
    Route::resource('users', UserController::class);
    Route::resource('goals', GoalController::class);

    // ✅ Laravel Breeze / auth route'ai (login, register, password, etc.)
    require __DIR__.'/auth.php';
});

// ✅ Kalbos perjungimas (paliekam kaip buvo)
Route::get('/lang/{locale}', function ($locale) {
    $availableLocales = config('app.available_locales', ['en', 'lt']);

    if (!in_array($locale, $availableLocales)) {
        abort(400);
    }

    Session::put('locale', $locale);

    $previousUrl = url()->previous();
    $parsed = parse_url($previousUrl);
    $segments = collect(explode('/', $parsed['path']))->filter()->values();

    if (isset($segments[0]) && in_array($segments[0], $availableLocales)) {
        $segments->forget(0);
    }

    $newPath = '/' . $locale . '/' . $segments->implode('/');

    return redirect($newPath);
})->name('lang.switch');
