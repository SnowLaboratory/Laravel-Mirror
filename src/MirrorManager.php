<?php

namespace SnowBuilds\Mirror;

use Exception;
use SnowBuilds\Mirror\Concerns\ScoringAlgorithms;
use Illuminate\Support\Str;
use Throwable;

class MirrorManager
{
    protected $strategies = [];
    protected $models = [];

    use ScoringAlgorithms;
    // Build your next great package.

    public function registerStrategy(string $in, string $out, ScoringStrategy $strategy)
    {
        $this->strategies[$in][$out] = $strategy;
        return $this;
    }

    public function register(string $model, $quiet=false)
    {
        $this->models[$model] = compact('model', 'quiet');
        return $this;
    }

    public function boot()
    {
        $classes = config('mirror.comparable', ['*']);
        if (data_get($classes, '0', '*') === '*') {
            foreach ($this->getModelNames() as $class) {
                $this->register($class, quiet: true);
            }
        } else {
            foreach ($classes as $class) {
                $this->register($class);
            }
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

    private function getModelNames(): array
    {
        return collect(array_keys(require('vendor/composer/autoload_classmap.php')))
            ->filter(function ($key) {
                return Str::startsWith($key, 'App\\Models\\');
            })->toArray();
    }
}
