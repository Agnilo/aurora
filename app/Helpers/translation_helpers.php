<?php
use App\Services\TranslationService;

if (! function_exists('t')) {
    function t(string $key, $replace = [], ?string $fallback = null)
    {

        if ($replace === null) {
            $replace = [];
        }

        if (is_string($replace)) {
            $fallback = $replace;
            $replace = [];
        }

        if (!is_array($replace)) {
            $replace = [];
        }

        $value = app(TranslationService::class)->get($key, $replace);

        if ($value === $key) {
            return $fallback ?? $key;
        }

        return $value;
    }
}
