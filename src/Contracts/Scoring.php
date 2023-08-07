<?php

namespace SnowBuilds\Mirror\Contracts;

use Illuminate\Database\Eloquent\Model;

interface Scoring
{
    /**
     * Compare the current model to a foreign model.
     */
    public function compare(Model $model):float;

    /**
     * Define comparison strategies for model.
     */
    public function registerComparison(string $key):void;
}