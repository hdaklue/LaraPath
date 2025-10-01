<?php

declare(strict_types=1);

namespace Hdaklue\PathBuilder\Utilities;

/**
 * Helper class for handling file extensions during sanitization.
 *
 * Provides utilities to separate, preserve, and reconstruct filenames
 * with extensions during path sanitization operations.
 */
final class ExtensionHelper
{
    /**
     * Known compound extensions that should be preserved together.
     *
     * @var array<string>
     */
    private static array $compoundExtensions = [
        'tar.gz',
        'tar.bz2',
        'tar.xz',
        'tar.z',
        'backup.sql',
        'backup.gz',
    ];

    /**
     * Separate filename and extension.
     *
     * @param  string  $filename  The filename to parse
     * @return array{name: string, extension: string|null} Array with 'name' and 'extension' keys
     */
    public static function separateExtension(string $filename): array
    {
        // Handle empty filename
        if (empty($filename)) {
            return ['name' => '', 'extension' => null];
        }

        // Check for compound extensions first
        foreach (self::$compoundExtensions as $compoundExt) {
            if (str_ends_with(strtolower($filename), '.'.$compoundExt)) {
                $extensionLength = strlen($compoundExt) + 1; // +1 for the dot
                $name = substr($filename, 0, -$extensionLength);

                // If name is empty or starts with dot (hidden file), treat as no extension
                if (empty($name) || $name[0] === '.') {
                    return ['name' => $filename, 'extension' => null];
                }

                return ['name' => $name, 'extension' => $compoundExt];
            }
        }

        // Find the last dot in the filename
        $lastDotPosition = strrpos($filename, '.');

        // If no dot found, or dot is at the beginning (hidden files), treat as no extension
        if ($lastDotPosition === false || $lastDotPosition === 0) {
            return ['name' => $filename, 'extension' => null];
        }

        // Extract name and extension
        $name = substr($filename, 0, $lastDotPosition);
        $extension = substr($filename, $lastDotPosition + 1);

        // If extension is empty or contains invalid characters, treat as no extension
        if (empty($extension) || ! self::isValidExtension($extension)) {
            return ['name' => $filename, 'extension' => null];
        }

        return ['name' => $name, 'extension' => $extension];
    }

    /**
     * Check if a string is a valid file extension.
     *
     * @param  string  $extension  The extension to validate
     * @return bool True if valid extension
     */
    public static function isValidExtension(string $extension): bool
    {
        // Extensions should be alphanumeric with possible numbers
        // Common extensions: jpg, png, pdf, docx, etc.
        return preg_match('/^[a-zA-Z0-9]+$/', $extension) === 1 && strlen($extension) <= 10;
    }

    /**
     * Reconstruct filename from name and extension.
     *
     * @param  string  $name  The filename without extension
     * @param  string|null  $extension  The extension (without dot)
     * @return string The reconstructed filename
     */
    public static function reconstructFilename(string $name, ?string $extension): string
    {
        if ($extension === null) {
            return $name;
        }

        return "{$name}.{$extension}";
    }

    /**
     * Apply sanitization to filename while preserving extension.
     *
     * @param  string  $filename  The original filename
     * @param  callable  $sanitizationCallback  Function to apply to the name part
     * @return string The sanitized filename with preserved extension
     */
    public static function sanitizeWithExtension(string $filename, callable $sanitizationCallback): string
    {
        $parts = self::separateExtension($filename);
        $sanitizedName = $sanitizationCallback($parts['name']);

        return self::reconstructFilename($sanitizedName, $parts['extension']);
    }
}
