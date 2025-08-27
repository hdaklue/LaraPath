<?php

declare(strict_types=1);

namespace Hdaklue\PathBuilder;

use Hdaklue\PathBuilder\Enums\SanitizationStrategy;
use Hdaklue\PathBuilder\Exceptions\InvalidSanitizationStrategyException;

/**
 * Path sanitization utility class.
 *
 * Handles the application of sanitization strategies to path segments
 * for secure and consistent path building.
 */
final class Sanitizer
{
    /**
     * Apply sanitization strategy to input string.
     *
     * @param  string  $input  Input string
     * @param  SanitizationStrategy|string  $strategy  Strategy to apply
     * @return string Sanitized string
     * @throws InvalidSanitizationStrategyException If strategy is invalid
     */
    public static function apply(string $input, SanitizationStrategy|string $strategy): string
    {
        $strategyClass = $strategy instanceof SanitizationStrategy ? $strategy->value : $strategy;

        if (! class_exists($strategyClass) || ! method_exists($strategyClass, 'apply')) {
            throw InvalidSanitizationStrategyException::create($strategyClass);
        }

        return $strategyClass::apply($input);
    }

    /**
     * Sanitize a single path segment.
     *
     * @param  string  $path  Path segment to sanitize
     * @return string Sanitized path segment
     */
    public static function sanitizePath(string $path): string
    {
        // Only remove harmless references, preserve .. for security detection
        $sanitized = str_replace(['./'], '', $path);

        return $sanitized === '.' ? '' : $sanitized;
    }

    /**
     * Trim leading and trailing slashes from a path segment.
     *
     * @param  string  $path  Path segment to trim
     * @return string Trimmed path segment
     */
    public static function trimSlashes(string $path): string
    {
        return trim($path, '/');
    }
}
