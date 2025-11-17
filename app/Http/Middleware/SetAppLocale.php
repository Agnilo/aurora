<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetAppLocale
{
    public function handle($request, Closure $next): Response
    {
        $available = config('app.available_locales', ['en', 'lt']);
        $first = $request->segment(1);

        // Jei URL prasideda locale → nustatom locale ir tęsiam
        if (in_array($first, $available)) {
            App::setLocale($first);
            Session::put('locale', $first);
            return $next($request);
        }

        // Jei URL neturi locale → pridėti jį prie viešo URL
        $locale = Session::get('locale', config('app.locale'));

        return redirect('/' . $locale . $request->getRequestUri());
    }
}