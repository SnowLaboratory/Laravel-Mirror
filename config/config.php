<?php

return [
    /**
     * List of model classes that are comparable.
     */
    'comparable' => ['*'],

    'algorithms' => [
        'words' => 'levenshtein',
        'lists' => 'euclidean',
        'numbers' => 'minMaxNorm',
    ],

    /**
     * Customize the models used internally.
     */
    'models' => [
        'recommendation' => \SnowBuilds\Mirror\Models\Recommendation::class,
    ],

    /**
     * Factories available to use in your project.
     */
    'factories' => [
        'recommendation' => \SnowBuilds\Mirror\Factories\RecommendationFactory::class,
    ],

    /**
     * Customize the table names used internally.
     */
    'table_names' => [
        'recommendations' => 'recommendations',
    ],

    /**
     * Customize the columns used internally.
     */
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