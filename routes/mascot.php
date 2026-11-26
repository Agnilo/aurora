<?php

use App\Http\Controllers\MascotController;
use App\Http\Middleware\SetAppLocale;
use Illuminate\Support\Facades\Route;

Route::prefix('{locale}')->middleware([SetAppLocale::class, 'auth'])->group(function () {
    Route::get('/mascot', [MascotController::class, 'edit'])->name('mascot.edit');
    Route::patch('/mascot', [MascotController::class, 'update'])->name('mascot.update');
});
