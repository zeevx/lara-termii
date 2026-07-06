<?php

declare(strict_types=1);

namespace Zeevx\LaraTermii;

use Illuminate\Support\ServiceProvider;

class LaraTermiiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/termii.php' => config_path('termii.php'),
            ], 'termii-config');
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/termii.php', 'termii');

        $this->app->singleton(LaraTermii::class, fn () => new LaraTermii);

        $this->app->alias(LaraTermii::class, 'lara-termii');
    }
}
