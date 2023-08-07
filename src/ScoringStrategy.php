<?php

namespace SnowBuilds\Mirror;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Facade;

/**
 * @see \SnowBuilds\Mirror\Skeleton\SkeletonClass
 */
class ScoringStrategy
{
    protected $strategy;

    public function using(string|callable $strategy) {
        $this->strategy = $strategy;
        return $this;
    }

    public function compare(Model $a, Model $b)
    {
        return App::call($this->strategy, compact('a', 'b'));
    }

    public function queued()
    {
        return $this;
    }

    public function nonQueued()
    {
        return $this;
    }
}
