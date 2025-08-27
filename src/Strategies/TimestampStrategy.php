<?php

declare(strict_types=1);

namespace Hdaklue\PathBuilder\Strategies;

use Hdaklue\PathBuilder\Contracts\SanitizationStrategyContract;

/**
 * Timestamp sanitization strategy.
 *
 * Appends Unix timestamp to input strings for uniqueness.
 * Useful for creating unique filenames and preventing naming conflicts.
 */
final class TimestampStrategy implements SanitizationStrategyContract
{
    /**
     * Apply timestamp sanitization to the input string.
     *
     * @param  string  $input  Input string to timestamp
     * @return string String with appended timestamp
     */
    public static function apply(string $input): string
    {
        return $input.'_'.time();
    }
}
