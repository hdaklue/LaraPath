<?php

declare(strict_types=1);

namespace Hdaklue\PathBuilder\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * LaraPath Facade
 *
 * @method static \Hdaklue\PathBuilder\PathBuilder base(string $path, \Hdaklue\PathBuilder\Enums\SanitizationStrategy|string|null $strategy = null)
 * @method static string build(array $segments)
 * @method static string join(string ...$segments)
 * @method static string normalize(string $path)
 * @method static bool isSafe(string $path)
 * @method static string buildRelativePath(string $absolutePath, string $basePath)
 * @method static string createSecureDirectoryName(string $input, string $algorithm = 'md5')
 * @method static string extractFilename(string $path)
 * @method static string getLastSegment(string $path)
 * @method static string getFileExtension(string $path)
 * @method static string extractFilenameWithoutExtension(string $path)
 * @method static string getDirectoryPathStatic(string $path)
 *
 * @see \Hdaklue\PathBuilder\PathBuilder
 */
class LaraPath extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'larapath';
    }
}