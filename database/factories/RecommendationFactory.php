<?php

namespace SnowBuilds\Mirror\Factories;

use Exception;
use Generator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use SnowBuilds\Mirror\Models\Recommendation;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class RecommendationFactory extends Factory
{

    protected static $models;
    protected static $recommended;
    protected static $crossProduct;
    protected static $types;
    protected static $generator;
    protected static $modelClass;
    protected static $recommendedClass;

    public function modelName()
    {
        return config('mirror.models.recommendation', Recommendation::class);
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            config('mirror.column_names.type', 'type') => fake()->randomElement(),
            config('mirror.column_names.score', 'score') => fake()->randomFloat(6, 0, 1),
            ...($this->randomRecommendation(static::$modelClass, static::$recommendedClass) ?? []),
        ];
    }

    protected static function getTypes()
    {
        return Arr::wrap(static::$types ?? 'default');
    }

    protected function getFactory($model)
    {
        return $model instanceof Factory ? $model : $model::factory();
    }

    protected function getClass($model)
    {
        return $model instanceof Factory ? $model->modelName() : $model;
    }

    public function types(array $types): self
    {
        static::$types = $types;
        return $this;
    }

    protected static function generator($modelClass, $recommendedClass): Generator
    {
        $recommended = $recommendedClass::inRandomOrder()->get();
        $models = $modelClass::inRandomOrder()->get();
        $crossProduct = $recommended
            ->crossJoin($models)
            ->crossJoin(static::getTypes())
            ->shuffle();

        foreach($crossProduct as $item) {
            // dd($item);
            $model = data_get($item, '0.0', data_get($item, '0'));
            $recommended = data_get($item, '0.1', data_get($item, '1'));
            yield [
                config('mirror.column_names.score', 'score') => fake()->randomFloat(6, 0, 1),
                config('mirror.column_names.model_id', 'model_id') => $model->{$model->getKeyName()},
                config('mirror.column_names.model_type', 'model_type') => get_class($model),
                config('mirror.column_names.recommended_id', 'recommended_id') =>$recommended->{$recommended->getKeyName()},
                config('mirror.column_names.recommended_type', 'recommended_type') => get_class($recommended),
                config('mirror.column_names.type', 'type') => data_get($item, '1'),
            ];
        }

        // for($i=0; $i<100; $i++) {
        //     yield [
        //         'id' => $i,
        //     ];
        // }
    }

    public function randomRecommendation($modelClass, $recommendedClass)
    {
        static::$generator ??= static::generator($modelClass, $recommendedClass);
        return tap(static::$generator->current(), function () {
            static::$generator->next();
        });
    }

    public function matrix(string $model, string|null $recommend=null)
    {
        $recommend ??= $model;
        static::$modelClass = $this->getClass($model);
        static::$recommendedClass = $this->getClass($recommend);
        return $this;
    }
}
