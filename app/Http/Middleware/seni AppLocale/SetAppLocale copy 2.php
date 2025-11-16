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

        if (in_array($locale, config('app.available_locales', ['en', 'lt']))) {
            App::setLocale($locale);
            Session::put('locale', $locale);
        } elseif (Session::has('locale')) {
            $locale = Session::get('locale');
            App::setLocale($locale);

            $segments = $request->segments();
            array_unshift($segments, $locale);

            return redirect()->to(implode('/', $segments));
        } else {
            App::setLocale(config('app.locale'));
        }

        return $next($request);
    }
}
