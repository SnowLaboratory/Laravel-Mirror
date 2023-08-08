<?php

namespace SnowBuilds\Mirror\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Oefenweb\DamerauLevenshtein\DamerauLevenshtein;
use Phpml\Exception\InvalidArgumentException;
use Phpml\Math\Distance\Euclidean;
use SnowBuilds\Mirror\Algorithm;
use SnowBuilds\Mirror\Models\Recommendation;

trait ScoringAlgorithms
{
    /**
     * Get the Hamming ratio between two strings;
     */
    public function hamming(string $propertyA, string|int|float $propertyB=null, string|int|float $weight=null): self
    {
        $weight ??= $propertyB ?? 1;
        $propertyB ??= $propertyA;
        $key = "{$propertyA}:{$propertyB}";

        return $this->using([
            $key => function ($a, $b) use($propertyA, $propertyB) {
                $valueA = data_get($a, $propertyA);
                $valueB = data_get($b, $propertyB);
                return Algorithm::hamming($valueA, $valueB);
            }
        ])->weight([
            $key => $weight
        ]);
    }

    /**
     * Get the Levenshtein ratio between two strings;
     */
    public static function levenshtein(string $propertyA, string|int|float $propertyB=null, string|int|float $weight=null)
    {
        $weight ??= $propertyB ?? 1;
        $propertyB ??= $propertyA;
        $key = "{$propertyA}:{$propertyB}";

        return $this->using([
            $key => function ($a, $b) use($propertyA, $propertyB) {
                $valueA = data_get($a, $propertyA);
                $valueB = data_get($b, $propertyB);
                return Algorithm::levenshtein($valueA, $valueB);
            }
        ])->weight([
            $key => $weight
        ]);
    }

    /**
     * Get the Damerau Levenshtein ratio between two strings;
     */
    public static function damerauLevenshtein(string $propertyA, string|int|float $propertyB=null, string|int|float $weight=null)
    {
        $weight ??= $propertyB ?? 1;
        $propertyB ??= $propertyA;
        $key = "{$propertyA}:{$propertyB}";

        return $this->using([
            $key => function ($a, $b) use($propertyA, $propertyB) {
                $valueA = data_get($a, $propertyA);
                $valueB = data_get($b, $propertyB);
                return Algorithm::damerauLevenshtein($valueA, $valueB);
            }
        ])->weight([
            $key => $weight
        ]);
    }

    public static function jaccard(string $propertyA, string|int|float $propertyB=null, string|int|float $weight=null): float
    {
        $weight ??= $propertyB ?? 1;
        $propertyB ??= $propertyA;
        $key = "{$propertyA}:{$propertyB}";

        return $this->using([
            $key => function ($a, $b) use($propertyA, $propertyB) {
                $valueA = data_get($a, $propertyA);
                $valueB = data_get($b, $propertyB);
                return Algorithm::jaccard($valueA, $valueB);
            }
        ])->weight([
            $key => $weight
        ]);
    }

    /**
     * Find the distance between two points.
     */
    public static function euclidian(string $propertyA, string|int|float $propertyB=null, string|int|float $weight=null)
    {
        $weight ??= $propertyB ?? 1;
        $propertyB ??= $propertyA;
        $key = "{$propertyA}:{$propertyB}";

        return $this->using([
            $key => function ($a, $b) use($propertyA, $propertyB) {
                $valueA = data_get($a, $propertyA);
                $valueB = data_get($b, $propertyB);
                return Algorithm::euclidian($valueA, $valueB);
            }
        ])->weight([
            $key => $weight
        ]);
    }

    /**
     * Normalize array of values based on the min and max.
     */
    public static function minMaxNorm(string $propertyA, string|int|float $propertyB=null, string|int|float $weight=null): array
    {
        $weight ??= $propertyB ?? 1;
        $propertyB ??= $propertyA;
        $key = "{$propertyA}:{$propertyB}";

        return $this->using([
            $key => function ($a, $b) use($propertyA, $propertyB) {
                $valueA = data_get($a, $propertyA);
                $valueB = data_get($b, $propertyB);
                return Algorithm::minMaxNorm($valueA, $valueB);
            }
        ])->weight([
            $key => $weight
        ]);
    }
}
