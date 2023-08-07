<?php

namespace SnowBuilds\Mirror\Concerns;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use SnowBuilds\Mirror\Models\Recommendation;

trait Recommendations
{
    use ScoringStrategies;

    public function scopeRecommendable(Builder $query)
    {
        return $query;
    }

    public function morphRecommendation($model, $recommendationType=null, $collectionType=null): MorphToMany
    {
        return $this->morphToMany(
            related: $model,
            name: config('mirror.column_names.model', 'model'),
            table: config('mirror.table_names.recommendations', 'recommendations'),
            relatedPivotKey: config('mirror.column_names.recommended_id', 'recommended_id'),
        )->orderBy('recommendations.score', 'desc')
        ->when(!is_null($recommendationType), function ($query) use ($recommendationType) {
            return $query->where(config('mirror.column_names.recommended_type', 'recommended_type'), $recommendationType);
        })
        ->when(!is_null($collectionType), function ($query) use ($collectionType) {
            return $query->where('type', $collectionType);
        })
        ->withPivot(['model_id', 'model_type', 'recommended_id', 'recommended_type', 'score', 'type'])
        ->using(config('mirror.models.recommendation', Recommendation::class))
        ;
    }

    public function recommendations(): BelongsToMany
    {
        return $this->morphRecommendation(static::class, collectionType:'default');
    }
}