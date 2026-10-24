<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SetAppLocale;
use App\Http\Middleware\SyncStreak;
use App\Providers\TranslationServiceProvider;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->redirectGuestsTo(function ($request) {
            return route('login', [
                'locale' => $request->route('locale') ?? app()->getLocale(),
            ]);
        });

        $middleware->redirectUsersTo(function ($request) {
            return route('dashboard', [
                'locale' => app()->getLocale(),
            ]);
        });

        $middleware->append([
            SetAppLocale::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
        ]);

        $middleware->web(append: [
            SyncStreak::class,
            \App\Http\Middleware\EnsureOnboardingCompleted::class,
        ]);
    })
    ->withProviders([
        TranslationServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
