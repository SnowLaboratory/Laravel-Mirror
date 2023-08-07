<?php

namespace SnowBuilds\Mirror\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Oefenweb\DamerauLevenshtein\DamerauLevenshtein;
use Phpml\Exception\InvalidArgumentException;
use Phpml\Math\Distance\Euclidean;
use SnowBuilds\Mirror\Models\Recommendation;

trait ScoringAlgorithms
{
    /**
     * Get the Hamming ratio between two strings;
     */
    public static function hamming(string $string1, string $string2, bool $returnDistance = false): float
    {
        $a        = str_pad($string1, strlen($string2) - strlen($string1), ' ');
        $b        = str_pad($string2, strlen($string1) - strlen($string2), ' ');
        $distance = count(array_diff_assoc(str_split($a), str_split($b)));

        if ($returnDistance) {
            return $distance;
        }
        return (strlen($a) - $distance) / strlen($a);
    }

    /**
     * Get the Levenshtein ratio between two strings;
     */
    public static function levenshtein(string $str1, string $str2)
    {
        $distance = levenshtein($str1, $str2);
        return 1 - ($distance / max(strlen($str1), strlen($str2)));
    }

    /**
     * Get the Damerau Levenshtein ratio between two strings;
     */
    public static function damerauLevenshtein(string $str1, string $str2)
    {
        $alg = new DamerauLevenshtein($str1, $str2);
        return $alg->getRelativeDistance();
    }

    public static function jaccard(string $string1, string $string2, string $separator = ','): float
    {
        $a            = explode($separator, $string1);
        $b            = explode($separator, $string2);
        $intersection = array_unique(array_intersect($a, $b));
        $union        = array_unique(array_merge($a, $b));

        return count($intersection) / count($union);
    }

    /**
     * Find the distance between two points.
     */
    public static function euclidian(array $point1, array $point2)
    {
        $alg = new Euclidean();
        $alg->distance($point1, $point2);
    }

    /**
     * Normalize array of values based on the min and max.
     */
    public static function minMaxNorm(array $values, $min = null, $max = null): array
    {
        $norm = [];
        $min  = $min ?? min($values);
        $max  = $max ?? max($values);

        foreach ($values as $value) {
            $numerator   = $value - $min;
            $denominator = $max - $min;
            $minMaxNorm  = $numerator / $denominator;
            $norm[]      = $minMaxNorm;
        }
        return $norm;
    }
}
