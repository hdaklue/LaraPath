<?php

declare(strict_types=1);

namespace Hdaklue\PathBuilder\Contracts;

/**
 * Contract for path sanitization strategies.
 *
 * All sanitization strategies must implement this contract
 * and provide a static apply method for memory efficiency.
 */
interface SanitizationStrategyContract
{
    /**
     * Apply sanitization to the input string.
     *
     * @param  string  $input  Input string to sanitize
     * @return string Sanitized string
     */
    public static function apply(string $input): string;
}
