<?php

use App\Http\Controllers\Admin\Gamification\CoinRewardAdminController;
use App\Http\Middleware\SetAppLocale;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => '{locale}/admin/gamification',
    'as' => 'admin.gamification.',
    'middleware' => ['auth', 'role:admin', SetAppLocale::class],
], function () {
    Route::get('/coin-rewards', [CoinRewardAdminController::class, 'edit'])->name('coin-rewards.edit');
    Route::put('/coin-rewards', [CoinRewardAdminController::class, 'update'])->name('coin-rewards.update');
});
