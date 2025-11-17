<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\TranslationAdminController;
use App\Models\Localization\Translation;

// MODEL BINDING FIX
Route::bind('translation', function ($value) {
    return Translation::findOrFail($value);
});

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {

        Route::get('/', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/translations/{translationKey}/edit', [TranslationAdminController::class, 'edit'])
            ->name('translations.edit');

        Route::put('/translations/{translationKey}', [TranslationAdminController::class, 'update'])
            ->name('translations.update');

        Route::delete('/translations/{translationKey}', [TranslationAdminController::class, 'destroy'])
            ->name('translations.destroy');

        Route::get('/translations', [TranslationAdminController::class, 'index'])
            ->name('translations.index');

        Route::get('/translations/create', [TranslationAdminController::class, 'create'])
            ->name('translations.create');

        Route::post('/translations', [TranslationAdminController::class, 'store'])
            ->name('translations.store');

        Route::get('/translations/export', [TranslationAdminController::class, 'export'])
            ->name('translations.export');

        Route::post('/translations/import', [TranslationAdminController::class, 'import'])
            ->name('translations.import');
    });




