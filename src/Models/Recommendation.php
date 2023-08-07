<?php

namespace SnowBuilds\Mirror\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\Pivot;
use SnowBuilds\Mirror\Factories\RecommendationFactory;

/**
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class Recommendation extends MorphPivot
{
    use HasFactory;

    protected $table = 'recommendations';

    protected $guarded = [];

    protected static function newFactory()
    {
        return resolve(config('mirror.factories.recommendation', RecommendationFactory::class));
    }

    public function model() {
        return $this->morphTo(
            name: config('mirror.column_names.model', 'model'),
        );
    }

    public function recommended() {
        return $this->morphTo(
            name: config('mirror.column_names.recommended', 'recommended'),
        );
    }
}