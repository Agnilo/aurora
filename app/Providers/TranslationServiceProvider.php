<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TranslationService;

class TranslationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('translation', function () {
            return new TranslationService();
        });
    }

    public function flushCache()
    {
        Cache::forget('translations.cache');
    }

    public function boot()
    {
        //
    }
}
