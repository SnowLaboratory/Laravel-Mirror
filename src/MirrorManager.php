<?php

namespace SnowBuilds\Mirror;

use Exception;
use Illuminate\Support\Collection;
use SnowBuilds\Mirror\Concerns\ScoringAlgorithms;
use Illuminate\Support\Str;
use Throwable;

class MirrorManager
{
    protected $strategies = [];
    protected $models = [];

    // Build your next great package.

    public function registerStrategy(string $in, string $out, ScoringStrategy $strategy)
    {
        $this->strategies[$in][$out] = $strategy;
        return $this;
    }

    public function strategies(string $in)
    {
        return collect($this->strategies[$in]);
    }

    public function register(string $model, $quiet=false)
    {
        $this->models[$model] = compact('model', 'quiet');
        return $this;
    }

    public function boot()
    {
        $quiet = $this->registeringAllModels();
        foreach ($this->models() as $class) {
            $this->register($class, quiet: $quiet);
        }

        foreach($this->models as $model) {
            $quiet = data_get($model, 'quiet', false);
            $model = resolve(data_get($model, 'model'));
            try {
                $model->registerRecommendations();
            } catch (Throwable $error) {
                if (! $quiet) {
                    throw $error;
                }
            }
        }
    }

    public function compare(string $in, string $out, $a, $b)
    {
        $strategy = data_get($this->strategies, [$in, $out], null);

        if (is_null($strategy)) {
            throw new Exception("Mirror comparison strategy not found");
        }

        return $strategy->compare($a, $b);
    }

    private function registeringAllModels()
    {
        $classes = config('mirror.comparable', ['*']);
        return data_get($classes, '0', '*') === '*';
    }

    public function models(): Collection
    {
        $classes = config('mirror.comparable', ['*']);

        if (! $this->registeringAllModels()) {
            return collect($classes);
        }

        return $this->classMap()
            ->filter(function ($key) {
                return Str::startsWith($key, 'App\\Models\\');
            });
    }

    private function classMap(): Collection
    {
        return collect(array_keys(require('vendor/composer/autoload_classmap.php')));
    }
}
