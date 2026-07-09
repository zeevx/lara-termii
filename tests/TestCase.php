<?php

declare(strict_types=1);

namespace Zeevx\LaraTermii\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Zeevx\LaraTermii\LaraTermiiServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaraTermiiServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('termii.api_key', 'test-api-key');
        $app['config']->set('termii.base_url', 'https://v4.api.termii.com');
        $app['config']->set('termii.sender_id', 'Acme');
        $app['config']->set('termii.channel', 'generic');
    }
}
