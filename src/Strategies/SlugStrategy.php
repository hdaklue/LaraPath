<?php

declare(strict_types=1);

namespace Hdaklue\PathBuilder\Strategies;

use Hdaklue\PathBuilder\Contracts\SanitizationStrategyContract;

/**
 * Slug sanitization strategy.
 *
 * Converts input strings to URL-friendly slug format.
 * Useful for creating web-safe paths and readable directory names.
 */
final class SlugStrategy implements SanitizationStrategyContract
{
    /**
     * Apply slug sanitization to the input string.
     *
     * @param  string  $input  Input string to slugify
     * @return string Slugified string
     */
    public static function apply(string $input): string
    {
        $cleaned = preg_replace('/[^a-zA-Z0-9\s-]/', '', $input);
        if ($cleaned === null) {
            return '';
        }

        return str($cleaned)->slug()->toString();
    }
}
