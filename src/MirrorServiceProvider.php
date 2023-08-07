<?php

namespace SnowBuilds\Mirror;

use Faker\Factory;
use Faker\Generator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use SnowBuilds\Mirror\Console\SyncMatrix;
use SnowBuilds\Mirror\Faker\FormatProvider;

class MirrorServiceProvider extends ServiceProvider
{
    /**
     * Register the package's services.
     */
    public function register(): void
    {
        $this->app->singleton(Mirror::class);
    }

    /**
     * Bootstrap the package's services.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerPublishing();
        Mirror::boot();
    }

    /**
     * Register the package's commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncMatrix::class,
            ]);
        }
    }

    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('mirror.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../database/migrations/create_recommendation_tables.php.stub' => $this->getMigrationFileName('create_recommendation_tables.php'),
            ], 'mirror-migrations');
        }
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     */
    protected function getMigrationFileName(string $migrationFileName): string
    {
        $timestamp = date('Y_m_d_His');

        $filesystem = $this->app->make(Filesystem::class);

        return Collection::make([$this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR])
            ->flatMap(fn ($path) => $filesystem->glob($path.'*_'.$migrationFileName))
            ->push($this->app->databasePath()."/migrations/{$timestamp}_{$migrationFileName}")
            ->first();
    }

}
