<?php

declare(strict_types=1);

namespace Hdaklue\PathBuilder\Strategies;

use Hdaklue\PathBuilder\Contracts\SanitizationStrategyContract;
use Hdaklue\PathBuilder\Utilities\ExtensionHelper;

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
     * Preserves file extensions while sanitizing the filename part.
     *
     * @param  string  $input  Input string to slugify
     * @return string Slugified string with preserved extension
     */
    public static function apply(string $input): string
    {
        return ExtensionHelper::sanitizeWithExtension($input, function (string $name): string {
            $cleaned = preg_replace('/[^a-zA-Z0-9\s-]/', '', $name);
            if ($cleaned === null) {
                return '';
            }

            return str($cleaned)->slug()->toString();
        });
    }
}
