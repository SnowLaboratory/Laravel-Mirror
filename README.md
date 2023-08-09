<p align="center"><img width="472" src="/art/laravel-mirror-banner.png" alt="Laravel Mirror Package Logo"></p>

<p align="center">
    <a href="https://packagist.org/packages/snowbuilds/laravel-mirror">
        <img src="https://img.shields.io/packagist/v/snowbuilds/laravel-mirror.svg?style=flat-square" alt="Latest Version on Packagist" />
    </a>
    <a href="https://packagist.org/packages/snowbuilds/laravel-mirror">
        <img src="https://img.shields.io/packagist/dt/snowbuilds/laravel-mirror.svg?style=flat-square" alt="Total Downloads" />
    </a>
    <a href="#">
        <img src="https://github.com/SnowLaboratory/Laravel-Mirror/actions/workflows/main.yml/badge.svg" alt="GitHub Actions" />
    </a>
</p>


- [Introduction](#introduction)
- [Installation](#installation)
- [Usage](#usage)
    - [Complex Example](#complex-usage)
- [Relationships](#relationships)
- [Generate Recommendations](#generate)
- [Roadmap](#roadmap)
- [Testing](#testing)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Security Vulnerabilities](#security)
- [Code of Conduct](#code-of-conduct)
- [License](#license)

<a name="introduction"></a>
## Introduction
Laravel Mirror is a quick way to intelligently suggest content to your users. It's great for recommending blog posts, products, recipes, etc. to bring your web app to the next level! Start by registing a recommendation strategy and routinely update recommendations in a CRON job. 

<a name="installation"></a>
## Installation

You can install the package via composer:

```bash
composer require "snowbuilds/laravel-mirror:^0.0.1-alpha"
```

```bash
php artisan vendor:publish --provider="SnowBuilds\Mirror\MirrorServiceProvider"
```

<a name="usage"></a>
## Usage
Registering a strategy is as simple as comparing two values. We added some utilities for quickly running a comparison between two models. For example getting the Levenshtein ratio between two blog post titles:

```php
use SnowBuilds\Mirror\Concerns\Recommendations;
use SnowBuilds\Mirror\Mirror;

class Post extends Model
{
    use Recommendations;

    public function registerRecommendations(): void
    {
        $this->registerStrategy(Post::class)
            ->levenshtein('title');
    }
}
```

<a name="weighted-averages"></a>
### Weighted Averages
Combining algorithms works too. Here we are using Levenshtein on blog titles and Euclidean on the tags. However, we want similar blog titles to score higher than tags, so we can add weights to either algorithm:
```php
public function registerRecommendations(): void
{
    $this->registerStrategy(Post::class)
        ->levenshtein('title', 2)
        ->euclidean('tags', 1);
}
```

<a name="different-properties-in-the-same-calculation"></a>
### Different Properties in the Same Calculation
Sometimes the property name is not the same for different models. Or you may want to compare different columns across the same model. This is also possible by adding a second parameter to the utility method:
```php
public function registerRecommendations(): void
{
    $this->registerStrategy(Post::class)
        ->levenshtein('title', 'name', 2)
        ->euclidean('tags', 'keywords', 1);
}
```

<a name="custom-scoring-algorithms"></a>
### Custom Scoring algorithms
For more advanced applications, the helper utilities are not enough. You can use your own algorithm by passing a closure or function pointer to the `using` method. The first value, `$a`, is the model that has recommendations, and the second value, `$b`, is the model being suggested:
```php
public function registerRecommendations(): void
{
    $this->registerStrategy(Post::class)
        ->using(function ($a, $b) {
            return Algorithm::levenshtein($a->name, $b->name);
        });
}
```

<a name="combining-weights-with-custom-algithms"></a>
### Combining Weights with Custom Algorithms
If your custom algorithm does not account for weights, you can specify an array of weights. 
```php
public function registerRecommendations(): void
{
    $this->registerStrategy(Post::class)
        ->using(function ($a, $b) {
            return Algorithm::levenshtein($a->title, $b->title);
        })
        ->using(function ($a, $b) {
            return Algorithm::euclidean($a->tags, $b->tags);
        })
        ->weights([2,1]);
}

```

<a name="managing-multiple-algorithms-and-weights"></a>
### Managing Multiple Algorithms and Weights
When using multiple custom algorithms and weights, the code can become hard to read. If you pass an associative array, you can keep track of which algorithms belong to which weights:
```php
public function registerRecommendations(): void
{
    $this->registerStrategy(Post::class)
        ->using([
            'titles' => fn ($a, $b) => Algorithm::levenshtein($a->title, $b->title),
            'tags' => fn ($a, $b) => Algorithm::levenshtein($a->tags, $b->tags),
        ])
        ->weights([
            'titles' => 2,
            'tags' => 1,
        ]);
}

```

<a name="complex-usage"></a>
### Complex scoring algorithm example
Anything that returns a number is valid, so if you wanted to suggest Products based on their features, price, categories, etc. You could take a weighted average of the scores for each comparison:
```php
use SnowBuilds\Mirror\Concerns\Recommendations;
use SnowBuilds\Mirror\Mirror;

class Product extends Model
{
    use Recommendations;

    public function registerRecommendations(): void
    {
        $this->registerStrategy(Product::class)
            ->using(function ($a, $b) {
                $aFeatures = implode('', get_object_vars($a->features));
                $bFeatures = implode('', get_object_vars($b->features));

                return array_sum([
                    Mirror::hamming($aFeatures, $bFeatures) * config('weights.feature'),
                    Mirror::euclidean(
                      Mirror::minMaxNorm([$a->price], 0, config('ranges.maxPrice')),
                      Mirror::minMaxNorm([$b->price], 0, config('ranges.maxPrice'))
                    ) * config('weights.price')),
                    Mirror::jaccard($a->categories, $b->categories) * config('weights.category'))
                ]) / (config('weights.feature') + config('weights.price') + config('weights.category'));
            });
    }
}

```

<a name="relationships"></a>
### Relationships
Once you populate the recommendations table, you can quickly access the recommended models by using the `morphsRecommendation` method. The relationship will order by the highest ranking score:

```php
class User extends Authenticatable
{
    use Recommendations;

    public function recommendedRecipes() {
        return $this->morphRecommendation(Recipe::class);
    }
}
```

<a name="generate"></a>
### Generating Recommendation Matrix
Computing recommendations can be resource intensive. Laravel Mirror provides a command for syncing recommendations. After syncing, your recommendation relationships will work:

```bash
php artisan mirror:sync
```

<a name="roadmap"></a>
## Roadmap
- [x] Blazingly Fast!
- [x] Polymorphic recommendations
- [x] Recommendation collections
- [x] Common comparison algorithms
- [x] Sync command
- [ ] Testing
- [ ] Programmatically invoke syncing actions
- [x] Simplified API for weights and faceted algorithms
- [ ] Queueing
- [ ] More algorithms
- [ ] More settings

<a name="testing"></a>
### Testing

```bash
composer test
```

<a name="changelog"></a>
### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

<a name="contributing"></a>
## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

<a name="security"></a>
### Security

If you discover any security related issues, please email dev@snowlaboratory.com instead of using the issue tracker.

## Code of Conduct
<a name="code-of-conduct"></a>

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

<a name="credits"></a>
## Credits

-   [Snow Labs](https://github.com/snowbuilds)
-   [Inspiration](https://oliverlundquist.com/2019/03/11/recommender-system-with-ml-in-laravel.html)
-   [All Contributors](../../contributors)

<a name="license"></a>
## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
