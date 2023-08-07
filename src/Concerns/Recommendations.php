<?php

namespace SnowBuilds\Mirror\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use SnowBuilds\Mirror\Models\Recommendation;

trait Recommendations
{
    use ScoringStrategies;

    public function morphRecommendation($model, $recommendationType=null, $collectionType=null)
    {
        return $this->morphToMany(
            related: $model,
            name: config('mirror.column_names.model', 'model'),
            table: config('mirror.table_names.recommendations', 'recommendations'),
            relatedPivotKey: config('mirror.column_names.model_id', 'model_id'),
        )->orderBy('recommendations.score', 'desc')
        ->when(!is_null($recommendationType), function ($query) use ($recommendationType) {
            return $query->where(config('mirror.column_names.recommended_type', 'recommended_type'), $recommendationType);
        })
        ->when(!is_null($collectionType), function ($query) use ($collectionType) {
            return $query->where('type', $collectionType);
        })
        ->using(config('mirror.models.recommendation', Recommendation::class))
        ;
    }

    public function recommendations(): BelongsToMany
    {
        return $this->morphRecommendation(static::class, collectionType:'default');
    }
}