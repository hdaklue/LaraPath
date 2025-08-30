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
     * Preserves file extensions when present.
     *
     * @param  string  $input  Input string to timestamp
     * @return string String with appended timestamp and preserved extension
     */
    public static function apply(string $input): string
    {
        // Check if the input has a file extension
        if (str_contains($input, '.') && pathinfo($input, PATHINFO_EXTENSION)) {
            $filename = pathinfo($input, PATHINFO_FILENAME);
            $extension = pathinfo($input, PATHINFO_EXTENSION);
            
            return $filename . '_' . time() . '.' . $extension;
        }
        
        // No extension, append timestamp normally
        return $input . '_' . time();
    }
}
