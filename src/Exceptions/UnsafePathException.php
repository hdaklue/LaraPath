<?php

declare(strict_types=1);

namespace Hdaklue\PathBuilder\Exceptions;

use InvalidArgumentException;

/**
 * Thrown when a path contains directory traversal attempts or unsafe patterns.
 */
class UnsafePathException extends InvalidArgumentException
{
    public static function create(string $path): self
    {
        return new self("Unsafe path detected: {$path}");
    }
}
