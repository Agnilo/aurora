<?php

namespace App\Services;

use App\Models\Localization\Translation;
use Illuminate\Support\Facades\Cache;

class TranslationService
{
    protected string $locale;
    protected string $fallback = 'en';

    public function __construct()
    {
        $this->locale = app()->getLocale();
    }

    public function get(string $fullKey, array $replace = [])
    {
        [$group, $key] = $this->splitKey($fullKey);

        $cached = $this->cachedTranslations();

        $value = $cached[$this->locale][$group][$key] ?? null;

        if (!$value && $this->locale !== $this->fallback) {
            $value = $cached[$this->fallback][$group][$key] ?? null;
        }

        if (!$value) return $fullKey;

        foreach ($replace as $k => $v) {
            $value = str_replace(':'.$k, $v, $value);
        }

        return $value;
    }

    protected function splitKey(string $fullKey)
    {
        $parts = explode('.', $fullKey, 2);

        return [
            $parts[0] ?? 'general',
            $parts[1] ?? $fullKey
        ];
    }

    protected function cachedTranslations()
    {
        return Cache::remember('translations.cache', 3600, function () {
            $all = Translation::all();
            $result = [];

            foreach ($all as $item) {
                $result[$item->language_code][$item->group][$item->key] = $item->value;
            }

            return $result;
        });
    }

    public function flushCache()
    {
        Cache::forget('translations.cache');
    }
}
