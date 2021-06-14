<?php

namespace Zeevx\LaraTermii;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Zeevx\LaraTermii\Skeleton\SkeletonClass
 */
class LaraTermiiFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'lara-termii';
    }
}
