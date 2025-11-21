<?php

if (!function_exists('ar')) {
    function ar($name, $param = null)
    {
        $locale = app()->getLocale();

        $data = ['locale' => $locale];

        $route = app('router')->getRoutes()->getByName($name);

        if ($route) {
            $paramNames = $route->parameterNames();
        } else {
            $paramNames = [];
        }

        if ($param !== null && isset($paramNames[1])) {
            $data[$paramNames[1]] = $param;
        }

        return route($name, $data);
    }
}


