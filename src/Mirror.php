<?php

namespace SnowBuilds\Mirror;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SnowBuilds\Mirror\Skeleton\SkeletonClass
 */
class Mirror extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return MirrorManager::class;
    }
}
