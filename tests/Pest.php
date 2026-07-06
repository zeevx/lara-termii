<?php

declare(strict_types=1);

use Zeevx\LaraTermii\Tests\TestCase;

uses(TestCase::class)->in('Feature');

/**
 * Build an expected endpoint URL from the configured base URL, so assertions
 * follow config('termii.base_url') instead of hardcoding the host.
 */
function termiiUrl(string $path): string
{
    return rtrim((string) config('termii.base_url'), '/').'/api/'.ltrim($path, '/');
}
