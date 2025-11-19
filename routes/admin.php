<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\TranslationAdminController;
use App\Http\Controllers\Admin\TranslationGroupController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Middleware\SetAppLocale;

// ADMIN ROUTES
Route::group([
    'prefix' => '{locale}/admin',
    'as' => 'admin.',
    'middleware' => ['auth', 'role:admin', SetAppLocale::class],
], function () {

    Route::get('/', [AdminDashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/translations/{translationKey}/edit', [TranslationAdminController::class, 'edit'])
        ->name('translations.edit');

    Route::put('/translations/{translationKey}', [TranslationAdminController::class, 'update'])
        ->name('translations.update');

    Route::get('/translations', [TranslationAdminController::class, 'index'])
        ->name('translations.index');

    Route::get('/translations/create', [TranslationAdminController::class, 'create'])
        ->name('translations.create');

    Route::post('/translations', [TranslationAdminController::class, 'store'])
        ->name('translations.store');

    Route::delete('/translations/{translationKey}', [TranslationAdminController::class, 'destroy'])
        ->name('translations.destroy');

    Route::get('/translations/export', [TranslationAdminController::class, 'export'])
        ->name('translations.export');

    Route::post('/translations/import', [TranslationAdminController::class, 'import'])
        ->name('translations.import');

    Route::resource('translation-groups', \App\Http\Controllers\Admin\TranslationGroupController::class);

    Route::get('/users', [\App\Http\Controllers\Admin\UserAdminController::class, 'index'])
        ->name('users.index');

    Route::get('/users/{user}/edit', [\App\Http\Controllers\Admin\UserAdminController::class, 'edit'])
        ->name('users.edit');

    Route::put('/users/{user}', [\App\Http\Controllers\Admin\UserAdminController::class, 'update'])
        ->name('users.update');

});
