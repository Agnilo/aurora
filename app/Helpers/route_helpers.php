<?php

if (!function_exists('ar')) {
    function ar($name, $param = null)
    {
        $locale = app()->getLocale();

        // 1) visada siunčiam locale
        $data = ['locale' => $locale];

        // 2) patikrinam route parametrus
        $route = app('router')->getRoutes()->getByName($name);

        if ($route) {
            $paramNames = $route->parameterNames(); // ['locale','translationKey']
        } else {
            $paramNames = [];
        }

        // 3) Jeigu route turi antrą parametrą — naudok jį
        if ($param !== null && isset($paramNames[1])) {
            $data[$paramNames[1]] = $param;   // ← ČIA ESENSIJA
        }

        return route($name, $data);
    }
}


