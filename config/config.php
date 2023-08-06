<?php

/*
 * You can place your custom package configuration in here.
 */

use SnowBuilds\Mirror\Factories\RecommendationFactory;
use SnowBuilds\Mirror\Models\Recommendation;

return [
    'models' => [
        'recommendation' => Recommendation::class,
    ],
    'factories' => [
        'recommendation' => RecommendationFactory::class,
    ],
    'table_names' => [
        'recommendations' => 'recommendations',
    ],
    'column_names' => [
        'model' => 'model',
        'model_id' => 'model_id',
        'model_type' => 'model_type',
        'recommended' => 'recommended',
        'recommended_id' => 'recommended_id',
        'recommended_type' => 'recommended_type',
        'type' => 'type',
        'score' => 'score',
    ],
];