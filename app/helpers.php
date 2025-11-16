<?php

if (!function_exists('t')) {
    function t($key, $replace = [])
    {
        return app('translation')->get($key, $replace);
    }
}
