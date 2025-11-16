<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetAppLocale
{
    public function handle($request, Closure $next)
    {
        $locale = $request->segment(1);

        // Patikrina ar locale yra galiojančias per URL
        if (in_array($locale, ['lt', 'en'])) {
            App::setLocale($locale);
            session(['app_locale' => $locale]); // išsaugom locale sesijoje
        }
        // Jei nėra URL prefix, bet yra sesijoje – naudok ją
        elseif (Session::has('app_locale')) {
            App::setLocale(Session::get('app_locale'));
        }
        // Kitaip naudok default locale
        else {
            App::setLocale(config('app.locale'));
        }

        return $next($request);
    }
}
