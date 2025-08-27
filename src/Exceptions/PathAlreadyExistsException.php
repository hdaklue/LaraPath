<?php

declare(strict_types=1);

namespace Hdaklue\PathBuilder\Exceptions;

use InvalidArgumentException;

/**
 * Thrown when a path already exists but is required not to exist.
 */
class PathAlreadyExistsException extends InvalidArgumentException
{
    public static function create(string $path, string $disk = 'local'): self
    {
        return new self("Path already exists: {$path} on disk: {$disk}");
    }
}
