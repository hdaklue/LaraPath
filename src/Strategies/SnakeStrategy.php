<?php

declare(strict_types=1);

namespace Hdaklue\PathBuilder\Strategies;

use Hdaklue\PathBuilder\Contracts\SanitizationStrategyContract;
use Hdaklue\PathBuilder\Utilities\ExtensionHelper;

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
        return ExtensionHelper::sanitizeWithExtension($input, function (string $name): string {
            return str($name)->snake()->toString();
        });
    }
}
