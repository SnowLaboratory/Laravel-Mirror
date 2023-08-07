<p align="center"><img width="472" src="/art/laravel-mirror-banner.png" alt="Laravel Mirror Package Logo"></p>


[![Latest Version on Packagist](https://img.shields.io/packagist/v/snowbuilds/laravel-mirror.svg?style=flat-square)](https://packagist.org/packages/snowbuilds/laravel-mirror)
[![Total Downloads](https://img.shields.io/packagist/dt/snowbuilds/laravel-mirror.svg?style=flat-square)](https://packagist.org/packages/snowbuilds/laravel-mirror)
![GitHub Actions](https://github.com/SnowLaboratory/Laravel-Mirror/actions/workflows/main.yml/badge.svg)

Build recommendation engines using pure Laravel and take your blog or web app to the next level!

Inspired by this [blog post](https://oliverlundquist.com/2019/03/11/recommender-system-with-ml-in-laravel.html)

## Installation

You can install the package via composer:

```bash
composer require snowbuilds/laravel-mirror
```

```bash
php artisan vendor:publish --provider="SnowBuilds\Mirror\MirrorServiceProvider"
```

## Why use Laravel Mirror
Laravel Mirror is perfect for quickly scaffolding algorithms for content suggestions. You can suggest related blog posts or products by registering a recommendation strategy. 

## Usage
Registering a strategy is as simple as comparing two values. The first value, `$a`, is the model that has recommendations, and the second value, `$b`, is the model being suggested:

```php
use SnowBuilds\Mirror\Concerns\Recommendations;
use SnowBuilds\Mirror\Mirror;

class Post extends Model
{
    use Recommendations;

    public function registerRecommendations(): void
    {
        $this->registerStrategy(Post::class)
            ->using(function ($post1, $post2) {
                return Mirror::levenshtein($post1->title, $post2->title);
            });
    }
}

```

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

### Generating Recommendation Matrix
Computing recommendations can be resource intensive. Laravel Mirror provides a command for syncing recommendations. After syncing, your recommendation relationships will work:

```bash
php artisan mirror:sync
```

## Roadmap
- [x] Blazingly Fast!
- [x] Polymorphic recommendations
- [x] Recommendation collections
- [x] Common comparison algorithms
- [x] Sync command
- [ ] Testing
- [ ] Programmatically invoke syncing actions
- [ ] Simplified API for weights and faceted algorithms
- [ ] Queueing
- [ ] More algorithms
- [ ] More settings


### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email zeb@snowlaboratory.com instead of using the issue tracker.

## Credits

-   [Snow Labs](https://github.com/snowbuilds)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
