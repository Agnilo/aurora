<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Http\Middleware\SetAppLocale;

use App\Http\Controllers\UserController;
use App\Http\Controllers\GoalController;

Route::get('/', function () {
        $defaultLocale = config('app.locale');
        return redirect("/{$defaultLocale}");
    });

// Lokalizuoti visi route'ai su /{locale} prefix
Route::group(['prefix' => '{locale}', 'middleware' => [SetAppLocale::class]], function () {

    Route::get('/', function () {
        return view('welcome');
    })->name('home');

    Route::get('/dashboard', function () {
        return redirect()->route('dashboard', ['locale' => $locale]);
    })->name('dashboard');

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    Route::resource('users', UserController::class);
    Route::resource('goals', GoalController::class);

    // Laravel Breeze / auth route'ai (login, register, password, etc.)
    require __DIR__.'/auth.php';
});

Route::get('/lang/{locale}', function ($locale) {
    $availableLocales = config('app.available_locales', ['en', 'lt']);

    if (!in_array($locale, $availableLocales)) {
        abort(400);
    }

    // Įrašom į sesiją pasirinktą kalbą
    Session::put('locale', $locale);

    // Grąžina vartotoją į tą patį puslapį, bet pakeitus locale URL prefix
    $previousUrl = url()->previous();
    $parsed = parse_url($previousUrl);

    // Ištraukiam kelią (path) be locale
    $segments = collect(explode('/', $parsed['path']))->filter()->values();

    // Pašalinam pirmą segmentą (locale)
    if (isset($segments[0]) && in_array($segments[0], $availableLocales)) {
        $segments->forget(0);
    }

    // Sudedam naują URL su pasirinkta kalba
    $newPath = '/' . $locale . '/' . $segments->implode('/');

    return redirect($newPath);
})->name('lang.switch');
