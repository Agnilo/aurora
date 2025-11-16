<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Http\Middleware\SetAppLocale;

// Lokalizuoti visi route'ai su /{locale} prefix
Route::group(['prefix' => '{locale}', 'middleware' => [SetAppLocale::class]], function () {

    Route::get('/', function () {
        return view('welcome');
    })->name('home');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    // Laravel Breeze / auth route'ai (login, register, password, etc.)
    require __DIR__.'/auth.php';
});

// Redirect iš šaknies be prefix į default kalbą
//Route::get('/', function () {
//    $defaultLocale = config('app.locale');
//    return redirect("/{$defaultLocale}");
//});

Route::get('/lang/{locale}', function ($locale) {
    if (! in_array($locale, config('app.available_locales'))) {
        abort(400);
    }

    Session::put('locale', $locale);
    return redirect()->back();
})->name('lang.switch');
