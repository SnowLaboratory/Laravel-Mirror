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
    - [Weighted Averages](#weighted-averages)
    - [Compare Different Properties](different-properties-in-the-same-calculation)
    - [Custom Scoring Algorithms](#custom-scoring-algorithms)
    - [Combining Weights & Custom Algorithms](#combining-weights-with-custom-algorithms)
    - [Organizing Code](#managing-multiple-algorithms-and-weights)
    - [Macros](#macros-extracting-algorithms)
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
Bring your user experience to the next level! Laravel Mirror lets you suggest content to your users intelligently.  Recommend blog posts, products, recipes, books, etc. Start by registering a recommendation strategy and routinely updating recommendations in a CRON job. 

<a name="installation"></a>
## Installation

You can install the package via composer:

```bash
composer require "snowbuilds/laravel-mirror:^0.0.3-alpha"
```

```bash
php artisan vendor:publish --provider="SnowBuilds\Mirror\MirrorServiceProvider"
```

<a name="usage"></a>
## Usage
Registering a strategy is as simple as comparing two values. We added some utilities for quick scaffolding. For example, recommending blog posts with similar titles:

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
Combining algorithms works too. Here we suggest posts with similar titles and tags. However, we want titles to rank higher than tags, so we add weights as a second argument to the algorithm utility:

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
Sometimes the property name is not the same for different models. Or you may want to compare different columns across the same model. For example, user's should see posts based on their biography and followed communities. Tags that match This is also possible by adding a second parameter to the utility method:

```php
class User extends Model
{
    use Recommendations;

    public function registerRecommendations(): void
    {
        $this->registerStrategy(Post::class)
            ->levenshtein('biography', 'title', 1) // compare biography to post title
            ->euclidean('communities', 'tags', 3); // compare communities to post tags
    }
}
```

<a name="custom-scoring-algorithms"></a>
### Custom Scoring algorithms
When the helper utilities are insufficient, you can invoke custom algorithms using the `using` method. The first value, `$a`, is the model that has recommendations, and the second value, `$b`, is the model being suggested:

```php
class User extends Model
{
    public function registerRecommendations(): void
    {
        $this->registerStrategy(Post::class)
            ->using(function (User $a, Post $b) {
                return Algorithm::levenshtein($a->name, $b->name);
            });
    }
}
```

<a name="combining-weights-with-custom-algorithms"></a>
### Combining Weights with Custom Algorithms
You can specify an array of weights when combining custom algorithms too. The weights are applied in the order that the algorithm was registered:

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
The code is hard to read when using multiple custom algorithms and weights. If you use an associative array, you can keep your algorithms and weights organized:

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

<a name="macros-extracting-algorithms"></a>
### Macros - Extracting Algorithms
When your custom algorithm is too cumbersome you can extract it into a macro. We use an internal utility for registering algorithms, which you are free to use in your macros:

```php
// ServiceProvider.php
ScoringStrategy::macro('huggingFace', function (...$args) {
  return $this->registerAlgorithm(
    fn($a, $b) => HuggingFace::invokeEmbedding($a, $b),
    ...$args
  );
});

// Model.php
class User extends Model 
{
    public function registerRecommendations(): void
    {
        $this->registerStrategy(User::class)
            ->euclidean('follewers')
            ->huggingFace('activity')
            ->levenshtein('bio');
    }
}
```

<a name="relationships"></a>
### Relationships
You can define a relationship between the model and the suggested content using the `morphsRecommendation` method. The relationship will order by the highest ranking score:

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
Calculating recommendations is resource-intensive. Laravel Mirror provides a command for syncing recommendations. After syncing, your recommendation relationships will work:

```bash
php artisan mirror:sync
```

In production, this should be a CRON job or registered in the Laravel kernel.
```php
class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('mirror:sync')->daily();
    }
}
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

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

<a name="contributing"></a>
## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

<a name="security"></a>
### Security

If you discover any security-related issues, please email dev@snowlaboratory.com instead of using the issue tracker.

## Code of Conduct
<a name="code-of-conduct"></a>

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

<a name="credits"></a>
## Credits

-   [Snow Labs](https://github.com/snowbuilds)
-   [Inspiration for Laravel Mirror](https://oliverlundquist.com/2019/03/11/recommender-system-with-ml-in-laravel.html)
-   [All Contributors](../../contributors)

<a name="license"></a>
## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
