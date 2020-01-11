<?php

namespace App\Providers;

use App\Extensions\Cache\FileStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Cache::extend('file2', function ($app, $config) {
            return Cache::repository(new FileStore($app['files'], $config['path']));
        });
    }
}
