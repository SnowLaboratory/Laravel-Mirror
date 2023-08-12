<?php

namespace SnowBuilds\Mirror\Concerns;

use BadMethodCallException;
use SnowBuilds\Mirror\Algorithm;

trait ScoringAlgorithms
{

    public function registerAlgorithm(callable $algorithm, string $propertyA, string|int|float $propertyB=null, string|int|float $weight=1)
    {
        if (is_int($propertyB) || is_float($propertyB)) {
            $weight ??= $propertyB;
            $propertyB = $propertyA;
        }

        $propertyB ??= $propertyA;
        $key = "{$propertyA}:{$propertyB}";

        return $this->using([
            $key => function ($a, $b) use($propertyA, $propertyB, $algorithm) {
                $valueA = data_get($a, $propertyA);
                $valueB = data_get($b, $propertyB);
                
                return call_user_func($algorithm, $valueA, $valueB);
            }
        ])->weight([
            $key => $weight
        ]);
    }

    /**
     * Get the Hamming ratio between two strings;
     */
    public function hamming(string $propertyA, string|int|float $propertyB=null, string|int|float $weight=null): self
    {
        return $this->registerAlgorithm(
            fn($valueA, $valueB) => Algorithm::hamming($valueA, $valueB),
            $propertyA, $propertyB, $weight
        );
    }

    /**
     * Get the Levenshtein ratio between two strings;
     */
    public function levenshtein(string $propertyA, string|int|float $propertyB=null, string|int|float $weight=null)
    {
        return $this->registerAlgorithm(
            fn($valueA, $valueB) => Algorithm::levenshtein($valueA, $valueB),
            $propertyA, $propertyB, $weight
        );
    }

    /**
     * Get the Damerau Levenshtein ratio between two strings;
     */
    public function damerauLevenshtein(string $propertyA, string|int|float $propertyB=null, string|int|float $weight=null)
    {
        return $this->registerAlgorithm(
            fn($valueA, $valueB) => Algorithm::damerauLevenshtein($valueA, $valueB),
            $propertyA, $propertyB, $weight
        );
    }

    public function jaccard(string $propertyA, string|int|float $propertyB=null, string|int|float $weight=null): float
    {
        return $this->registerAlgorithm(
            fn($valueA, $valueB) => Algorithm::jaccard($valueA, $valueB),
            $propertyA, $propertyB, $weight
        );
    }

    /**
     * Find the distance between two points.
     */
    public function euclidean(string $propertyA, string|int|float $propertyB=null, string|int|float $weight=null)
    {
        return $this->registerAlgorithm(
            fn($valueA, $valueB) => Algorithm::euclidean($valueA, $valueB),
            $propertyA, $propertyB, $weight
        );
    }

    /**
     * Normalize array of values based on the min and max.
     */
    public function minMaxNorm(string $propertyA, string|int|float $propertyB=null, string|int|float $weight=null): array
    {
        return $this->registerAlgorithm(
            fn($valueA, $valueB) => Algorithm::minMaxNorm($valueA, $valueB),
            $propertyA, $propertyB, $weight
        );
    }

    public function __call($method, $parameters)
    {
        $algorithms = config('mirror.algorithms', [
            'words' => 'levenshtein',
            'lists' => 'euclidean',
            'numbers' => 'minMaxNorm',
        ]);

        $algorithm = data_get($algorithms, $method);

        if (is_null($algorithm)) {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.', static::class, $method
            ));
        }

        return call_user_func($algorithm, $parameters);
    }
}
