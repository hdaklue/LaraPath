<?php

declare(strict_types=1);

namespace Hdaklue\PathBuilder;

use Illuminate\Support\ServiceProvider;

/**
 * PathBuilder Laravel Service Provider
 *
 * Provides optional Laravel integration for the PathBuilder package.
 * Auto-discovered by Laravel when the package is installed.
 */
class PathBuilderServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('pathbuilder', fn () => new PathBuilder);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Service provider is mainly for auto-discovery
        // PathBuilder works as a static utility class
    }
}
