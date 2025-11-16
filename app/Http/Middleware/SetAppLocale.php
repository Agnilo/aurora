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
        $locale = $request->segment(1);
        $available = config('app.available_locales', ['en', 'lt']);

        if (in_array($locale, $available)) {
            App::setLocale($locale);
            Session::put('locale', $locale);
        }

        elseif (Session::has('locale')) {
            $locale = Session::get('locale');
            App::setLocale($locale);

            $segments = $request->segments();
            array_unshift($segments, $locale);
            return redirect()->to(implode('/', $segments));
        }

        else {
            $locale = config('app.locale');
            App::setLocale($locale);

            $segments = $request->segments();
            array_unshift($segments, $locale);
            return redirect()->to(implode('/', $segments));
        }

        return $next($request);
    }
}