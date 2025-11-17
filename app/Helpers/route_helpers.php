<?php

if (!function_exists('ar')) {
    /**
     * Generate a localized route URL.
     * Works with:
     *   - no params
     *   - one param (id or key string)
     *   - array of params
     */
    function ar(string $name, $params = [])
    {
        $locale = app()->getLocale();

        $route = app('router')->getRoutes()->getByName($name);
        if (!$route) {
            throw new Exception("Route [$name] not found.");
        }

        // Route parameters (e.g. ["translationKey"])
        $paramNames = $route->parameterNames();

        $data = ['locale' => $locale];

        // jei $params yra NOT array → vienas parametras
        if (!is_array($params)) {
            $data[$paramNames[0]] = $params;
            return route($name, $data);
        }

        // jei masyvas → matchinam vardus
        foreach ($paramNames as $i => $param) {
            if ($i == 0) continue; // skip locale
            if (isset($params[$param])) {
                $data[$param] = $params[$param];
            }
        }

        return route($name, $data);
    }

}

