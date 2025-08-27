<?php

declare(strict_types=1);

namespace Hdaklue\PathBuilder\Exceptions;

use InvalidArgumentException;

/**
 * Thrown when a required path does not exist on the specified storage disk.
 */
class PathNotFoundException extends InvalidArgumentException
{
    public static function create(string $path, string $disk = 'local'): self
    {
        return new self("Path does not exist: {$path} on disk: {$disk}");
    }
}
