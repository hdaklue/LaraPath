<?php

declare(strict_types=1);

namespace Hdaklue\PathBuilder\Strategies;

use Hdaklue\PathBuilder\Contracts\SanitizationStrategyContract;

/**
 * Snake case sanitization strategy.
 *
 * Converts input strings to snake_case format.
 * Useful for creating filesystem-safe names that maintain readability.
 */
final class SnakeStrategy implements SanitizationStrategyContract
{
    /**
     * Apply snake_case sanitization to the input string.
     * Preserves file extensions when present.
     *
     * @param  string  $input  Input string to convert
     * @return string Snake_case string with preserved extension
     */
    public static function apply(string $input): string
    {
        // Check if the input has a file extension
        if (str_contains($input, '.') && pathinfo($input, PATHINFO_EXTENSION)) {
            $filename = pathinfo($input, PATHINFO_FILENAME);
            $extension = pathinfo($input, PATHINFO_EXTENSION);

            return str($filename)->snake()->toString().'.'.$extension;
        }

        // No extension, apply snake_case normally
        return str($input)->snake()->toString();
    }
}
