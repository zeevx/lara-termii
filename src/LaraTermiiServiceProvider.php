<?php

namespace Zeevx\LaraTermii;

use Illuminate\Support\ServiceProvider;

class LaraTermiiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {

    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Register the main class to use with the facade
        $this->app->singleton('lara-termii', function () {
            return new LaraTermii;
        });
    }
}
