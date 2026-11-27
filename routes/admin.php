<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\TranslationAdminController;
use App\Http\Controllers\Admin\TranslationGroupController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\LookupAdminController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\Gamification\GamificationDashboardController;
use App\Http\Controllers\Admin\Gamification\BadgeAdminController;
use App\Http\Controllers\Admin\Gamification\BadgeCategoryAdminController;
use App\Http\Controllers\Admin\Gamification\BonusAdminController;
use App\Http\Controllers\Admin\Gamification\BonusContextAdminController;
use App\Http\Controllers\Admin\Gamification\GoalBonusAdminController;
use App\Http\Controllers\Admin\Gamification\LevelAdminController;
use App\Http\Controllers\Admin\Gamification\XpRuleAdminController;
use App\Http\Controllers\Admin\Preferences\AssessmentQuestionAdminController;
use App\Http\Controllers\Admin\Gamification\MascotItemAdminController;

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

        Route::get('/{translationGroup}/{translationKey}/edit', [TranslationAdminController::class, 'edit'])
            ->name('edit');
        Route::put('/{translationGroup}/{translationKey}', [TranslationAdminController::class, 'update'])
            ->name('update');

        Route::get('/', [TranslationAdminController::class, 'index'])
            ->name('index');
        Route::get('/create', [TranslationAdminController::class, 'create'])
            ->name('create');
        Route::post('/', [TranslationAdminController::class, 'store'])
            ->name('store');
        Route::delete('/{translationGroup}/{translationKey}', [TranslationAdminController::class, 'destroy'])
            ->name('destroy');
        Route::delete('/bulk', [TranslationAdminController::class, 'bulkDestroy'])
            ->name('bulk-destroy');

        Route::get('/export', [TranslationAdminController::class, 'export'])
            ->name('export');
        Route::post('/import', [TranslationAdminController::class, 'import'])
            ->name('import');

    });

    Route::resource('translation-groups', TranslationGroupController::class)->except(['show']);

    Route::prefix('users')->as('users.')->group(function () {

        Route::get('/', [UserAdminController::class, 'index'])
            ->name('index');

        Route::get('/{user}/edit', [UserAdminController::class, 'edit'])
            ->name('edit');

        Route::put('/{user}', [UserAdminController::class, 'update'])
            ->name('update');

        Route::put('/{user}/preferences', [UserAdminController::class, 'updatePreferences'])
            ->name('preferences.update');

    });

    Route::prefix('lookups')->as('lookups.')->group(function () {

        Route::get('/', [LookupAdminController::class, 'index'])->name('index');


        Route::get('/{section}/create/{type}', [LookupAdminController::class, 'create'])->name('create');
        Route::post('/{section}/create/{type}', [LookupAdminController::class, 'store'])->name('store');

        Route::get('/{section}/{type}/{id}/edit', [LookupAdminController::class, 'edit'])->name('edit');
        Route::put('/{section}/{type}/{id}', [LookupAdminController::class, 'update'])->name('update');

        Route::delete('/{section}/{type}/{id}', [LookupAdminController::class, 'destroy'])->name('destroy');

        Route::post('/{section}/{type}/reorder', [LookupAdminController::class, 'reorder'])
            ->name('lookups.reorder');

    });

    Route::resource('languages', LanguageController::class)->except(['show']);

    Route::prefix('preferences/questions')
        ->as('preferences.questions.')
        ->group(function () {

            Route::get('/', [AssessmentQuestionAdminController::class, 'index'])
                ->name('index');

            Route::get('/{question}/edit', [AssessmentQuestionAdminController::class, 'edit'])
                ->name('edit');

            Route::put('/{question}', [AssessmentQuestionAdminController::class, 'update'])
                ->name('update');

        });

    Route::prefix('gamification')->as('gamification.')->group(function () {

        Route::get('/', [GamificationDashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('levels', LevelAdminController::class)->except(['show']);
        Route::resource('xp-rules', XpRuleAdminController::class)->only(['index', 'edit', 'update']);
        Route::resource('bonuses', BonusAdminController::class)->only(['index', 'create', 'store', 'edit', 'update']);
        Route::resource('badges', BadgeAdminController::class)->except(['show']);
        Route::resource('bonus-contexts', BonusContextAdminController::class)
            ->except(['show', 'destroy']);
        Route::resource('badge-categories', BadgeCategoryAdminController::class)
            ->except(['show', 'destroy']);

        Route::post('mascot-items/bulk', [MascotItemAdminController::class, 'bulk'])
            ->name('mascot-items.bulk');
        Route::resource('mascot-items', MascotItemAdminController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    });


});
