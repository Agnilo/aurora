<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\TranslationAdminController;
use App\Http\Controllers\Admin\TranslationGroupController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\LookupAdminController;
use App\Http\Controllers\Admin\LanguageController;

use App\Http\Middleware\SetAppLocale;

// ADMIN ROUTES
Route::group([
    'prefix' => '{locale}/admin',
    'as' => 'admin.',
    'middleware' => ['auth', 'role:admin', SetAppLocale::class],
], function () {

    Route::get('/', [AdminDashboardController::class, 'index'])
        ->name('dashboard');

    Route::prefix('translations')->as('translations.')->group(function () {

        Route::get('/{translationKey}/edit', [TranslationAdminController::class, 'edit'])
            ->name('edit');
        Route::put('/{translationKey}', [TranslationAdminController::class, 'update'])
            ->name('update');

        Route::get('/', [TranslationAdminController::class, 'index'])
            ->name('index');
        Route::get('/create', [TranslationAdminController::class, 'create'])
            ->name('create');
        Route::post('/', [TranslationAdminController::class, 'store'])
            ->name('store');
        Route::delete('/{translationKey}', [TranslationAdminController::class, 'destroy'])
            ->name('destroy');

        Route::get('/export', [TranslationAdminController::class, 'export'])
            ->name('export');
        Route::post('/import', [TranslationAdminController::class, 'import'])
            ->name('import');

    });

    Route::resource('translation-groups', TranslationGroupController::class);

    Route::prefix('users')->as('users.')->group(function () {

        Route::get('/', [UserAdminController::class, 'index'])
            ->name('index');

        Route::get('/{user}/edit', [UserAdminController::class, 'edit'])
            ->name('edit');

        Route::put('/{user}', [UserAdminController::class, 'update'])
            ->name('update');

    });

    Route::prefix('lookups')->as('lookups.')->group(function () {
        
        Route::get('/', [LookupAdminController::class, 'index'])->name('index');

        Route::get('/{section}', [LookupAdminController::class, 'section'])->name('section');

        Route::get('/{section}/create/{type}', [LookupAdminController::class, 'create'])->name('create');
        Route::post('/{section}/create/{type}', [LookupAdminController::class, 'store'])->name('store');

        Route::get('/{section}/{type}/{id}/edit', [LookupAdminController::class, 'edit'])->name('edit');
        Route::put('/{section}/{type}/{id}', [LookupAdminController::class, 'update'])->name('update');

        Route::delete('/{section}/{type}/{id}', [LookupAdminController::class, 'destroy'])->name('destroy');

        Route::post('/{section}/{type}/reorder', [LookupAdminController::class, 'reorder'])
            ->name('lookups.reorder');

    });

    Route::resource('languages', LanguageController::class);

});
