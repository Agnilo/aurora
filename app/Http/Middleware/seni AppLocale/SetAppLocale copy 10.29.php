<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetAppLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->segment(1); // /lt, /en ir pan.

        if (in_array($locale, ['lt', 'en'])) {
            App::setLocale($locale);
        } else {
            App::setLocale(config('app.locale')); // fallback į default
        }

        return $next($request);
    }
}
