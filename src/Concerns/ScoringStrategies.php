<?php

namespace SnowBuilds\Mirror\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use SnowBuilds\Mirror\Mirror;
use SnowBuilds\Mirror\Models\Recommendation;
use SnowBuilds\Mirror\ScoringStrategy;

trait ScoringStrategies
{

    public function compare(Model $model):float
    {
        return Mirror::compare(static::class, get_class($model), $this, $model);
    }

    public function registerStrategy(string $key=null): ScoringStrategy
    {
        return tap(new ScoringStrategy($key), function ($strategy) use ($key) {
            Mirror::registerStrategy(static::class, $key, $strategy);
        });
    }
}