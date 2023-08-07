<?php

namespace SnowBuilds\Mirror;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Facade;
use Throwable;

/**
 * @see \SnowBuilds\Mirror\Skeleton\SkeletonClass
 */
class ScoringStrategy
{
    protected $strategy;
    protected $query;
    protected $model;
    protected $collection = 'default';
    public string $key;

    public function __construct($key)
    {
        $this->key = $key;
        try {
            $this->usingModel($key);
        } catch (Throwable $ignore) { }
    }

    public function using(string|callable $strategy) {
        $this->strategy = $strategy;
        return $this;
    }

    public function usingModel($model) {
        $this->model = resolve($model);
        if (! $this->model instanceof Model) {
            throw new Exception("Not instance of Model");
        }
        return $this;
    }

    public function usingCollection(string $collectionName) {
        $this->collection = $collectionName;
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

    public function queryUsing(callable $query)
    {
        $this->query = $query;
        return $this;
    }

    public function model(): Model
    {
        return $this->model;
    }

    public function collection(): string
    {
        return $this->collection;
    }

    public function query()
    {
        if ($this->query) {
            $query = call_user_func([$this->model(), 'query']);
            return call_user_func($this->query, $query);
        }

        if (method_exists($this->model(), 'scopeRecommendable')) {
            return call_user_func([$this->model(), 'recommendable']);
        }

        return call_user_func([$this->model(), 'query']);
    }
}
