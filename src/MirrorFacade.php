<?php

namespace Snowbuilds\Mirror;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Snowbuilds\Mirror\Skeleton\SkeletonClass
 */
class MirrorFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-mirror';
    }
}
