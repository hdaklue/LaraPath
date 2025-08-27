<?php

declare(strict_types=1);

namespace Hdaklue\PathBuilder\Strategies;

use Hdaklue\PathBuilder\Contracts\SanitizationStrategyContract;

/**
 * Hashed sanitization strategy.
 *
 * Converts input strings to hash values for obfuscation and security.
 * Useful for user IDs, sensitive data, or creating uniform directory names.
 */
final class HashedStrategy implements SanitizationStrategyContract
{
    /**
     * Apply hash sanitization to the input string.
     *
     * @param  string  $input  Input string to hash
     * @param  string  $algorithm  Hash algorithm (default: md5)
     * @return string Hashed string
     */
    public static function apply(string $input, string $algorithm = 'md5'): string
    {
        return hash($algorithm, $input);
    }
}
