<?php

namespace SnowBuilds\Mirror;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Traits\Macroable;
use SnowBuilds\Mirror\Concerns\ScoringAlgorithms;
use Throwable;

/**
 * @see \SnowBuilds\Mirror\Skeleton\SkeletonClass
 */
class ScoringStrategy
{
    use ScoringAlgorithms {
        ScoringAlgorithms::__call as algorithmCall;
    }

    use Macroable {
        Macroable::__call as macroCall;
    }
    
    protected array $comparators;
    protected array $weights;
    protected $query;
    protected $model;
    protected $collection = 'default';
    public string $key;

    public function __construct($key)
    {
        $this->key = $key;
        $this->comparators = [];
        $this->weights = [];

        try {
            $this->usingModel($key);
        } catch (Throwable $ignore) { }
    }

    public function using(array|string|callable $comparator) {
        $this->comparators = array_merge($this->comparators, Arr::wrap($comparator));
        return $this;
    }

    public function weight(array|int|float $weight) {
        $this->weights = array_merge($this->weights, Arr::wrap($weight));
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
        $sum = 0;
        $this->weights = empty($this->weights) ? [1] : $this->weights;
        $totalWeight = array_sum($this->weights);

        if ($totalWeight === 0) {
            return null;
        }

        foreach($this->comparators as $key => $comparator) {
            $weight = data_get($this->weights, $key, 1);
            $value = App::call($comparator, compact('a', 'b'));
            $sum += $value * $weight;
        }

        return $sum / $totalWeight;
    }

    public function queued()
    {
        throw new Exception('Feature not implemented');
        return $this;
    }

    public function nonQueued()
    {
        throw new Exception('Feature not implemented');
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

    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        return $this->algorithmCall($method, $parameters);
        // $this->__macroable_call($method, $parameters);
        // $this->__
    }
}
