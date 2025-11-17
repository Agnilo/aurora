<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use App\Http\Middleware\SetAppLocale;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;

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

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['auth', 'verified'])
        ->name('dashboard');

    Route::get('/goals/category/{category}', [CategoryController::class, 'show'])
        ->name('category.show');

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    Route::resource('users', UserController::class);
    Route::resource('goals', GoalController::class);

    require __DIR__.'/auth.php';
});
