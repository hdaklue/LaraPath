<?php

declare(strict_types=1);

namespace Hdaklue\PathBuilder\Exceptions;

use InvalidArgumentException;

/**
 * Thrown when an invalid or non-existent sanitization strategy is used.
 */
class InvalidSanitizationStrategyException extends InvalidArgumentException
{
    public static function create(string $strategy): self
    {
        return new self("Strategy class {$strategy} not found or doesn't implement apply() method");
    }

    public static function createForMissingContract(string $strategy): self
    {
        return new self("Strategy class {$strategy} must implement SanitizationStrategyContract interface");
    }
}
