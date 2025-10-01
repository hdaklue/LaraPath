<?php

declare(strict_types=1);

namespace Hdaklue\PathBuilder;

use Hdaklue\PathBuilder\Enums\SanitizationStrategy;
use Hdaklue\PathBuilder\Exceptions\InvalidHierarchyException;
use Hdaklue\PathBuilder\Exceptions\PathAlreadyExistsException;
use Hdaklue\PathBuilder\Exceptions\PathNotFoundException;
use Hdaklue\PathBuilder\Exceptions\UnsafePathException;
use Hdaklue\PathBuilder\Utilities\ExtensionHelper;
use Hdaklue\PathBuilder\Utilities\FileSize;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Path Builder Utility with Fluent API
 *
 * Provides consistent path building and manipulation using Laravel's File facade
 * and string helpers for better maintainability and consistency.
 *
 * Supports fluent interface with sanitization strategies for secure path building.
 */
final class PathBuilder
{
    private array $segments = [];

    private bool $fileAdded = false;

    /**
     * Create a new PathBuilder instance starting with a base path.
     *
     * @param  string  $path  Base path
     * @param  SanitizationStrategy|string|null  $strategy  Sanitization strategy
     * @return self New PathBuilder instance
     */
    public static function base(string $path, SanitizationStrategy|string|null $strategy = null): self
    {
        $instance = new self;
        $sanitizedPath = $strategy ? Sanitizer::apply($path, $strategy) : $path;

        // Split the path into segments if it contains slashes
        $pathSegments = explode('/', Sanitizer::trimSlashes($sanitizedPath));
        foreach ($pathSegments as $segment) {
            if ($segment !== '') {
                $instance->segments[] = Sanitizer::sanitizePath($segment);
            }
        }

        return $instance;
    }

    /**
     * Add a path segment with optional sanitization strategy.
     *
     * @param  string  $name  Path segment to add
     * @param  SanitizationStrategy|string|null  $strategy  Sanitization strategy
     * @return self New instance for chaining
     * @throws InvalidHierarchyException If a file has already been added
     */
    public function add(string $name, SanitizationStrategy|string|null $strategy = null): self
    {
        if ($this->fileAdded) {
            throw InvalidHierarchyException::create();
        }

        $newInstance = clone $this;
        $sanitizedName = $strategy ? Sanitizer::apply($name, $strategy) : $name;
        $newInstance->segments[] = Sanitizer::sanitizePath(Sanitizer::trimSlashes($sanitizedName));

        return $newInstance;
    }

    /**
     * Add a file with optional sanitization strategy.
     *
     * @param  string  $filename  Filename to add
     * @param  SanitizationStrategy|string|null  $strategy  Sanitization strategy
     * @return self Current instance for chaining
     */
    public function addFile(string $filename, SanitizationStrategy|string|null $strategy = null): self
    {
        $sanitizedFilename = $strategy ? Sanitizer::apply($filename, $strategy) : $filename;
        $this->segments[] = Sanitizer::sanitizePath(Sanitizer::trimSlashes($sanitizedFilename));
        $this->fileAdded = true;

        return $this;
    }

    /**
     * Add a timestamped directory segment.
     *
     * @return self Current instance for chaining
     * @throws InvalidHierarchyException If a file has already been added
     */
    public function addTimestampedDir(): self
    {
        if ($this->fileAdded) {
            throw InvalidHierarchyException::create();
        }

        $this->segments[] = (string) time();

        return $this;
    }

    /**
     * Add a hashed directory segment.
     *
     * @param  string  $input  Input to hash
     * @param  string  $algorithm  Hash algorithm
     * @return self Current instance for chaining
     * @throws InvalidHierarchyException If a file has already been added
     */
    public function addHashedDir(string $input, string $algorithm = 'md5'): self
    {
        if ($this->fileAdded) {
            throw InvalidHierarchyException::create();
        }

        $this->segments[] = hash($algorithm, Sanitizer::trimSlashes($input));

        return $this;
    }

    /**
     * Replace the extension of the last segment (if it's a file).
     * Properly handles compound extensions like .tar.gz
     *
     * @param  string  $newExt  New extension (with or without dot)
     * @return self New instance for chaining
     */
    public function replaceExtension(string $newExt): self
    {
        $newInstance = clone $this;

        if (empty($newInstance->segments)) {
            return $newInstance;
        }

        $lastIndex = count($newInstance->segments) - 1;
        $lastSegment = $newInstance->segments[$lastIndex];

        $parts = ExtensionHelper::separateExtension($lastSegment);

        if ($parts['extension'] !== null) {
            $newExt = ltrim($newExt, '.');
            $newInstance->segments[$lastIndex] = ExtensionHelper::reconstructFilename($parts['name'], $newExt);
        }

        return $newInstance;
    }

    /**
     * Get the extension of the current path.
     * Properly handles compound extensions like .tar.gz
     *
     * @return string File extension or empty string
     */
    public function getExtension(): string
    {
        if (empty($this->segments)) {
            return '';
        }

        $lastIndex = count($this->segments) - 1;
        $lastSegment = $this->segments[$lastIndex];

        $parts = ExtensionHelper::separateExtension($lastSegment);

        return $parts['extension'] ?? '';
    }

    /**
     * Get the filename from the current path.
     *
     * @return string Filename or empty string
     */
    public function getFilename(): string
    {
        if (empty($this->segments)) {
            return '';
        }

        $lastIndex = count($this->segments) - 1;

        return $this->segments[$lastIndex];
    }

    /**
     * Get the filename without extension from the current path.
     * Properly handles compound extensions like .tar.gz
     *
     * @return string Filename without extension or empty string
     */
    public function getFilenameWithoutExtension(): string
    {
        if (empty($this->segments)) {
            return '';
        }

        $lastIndex = count($this->segments) - 1;
        $lastSegment = $this->segments[$lastIndex];

        $parts = ExtensionHelper::separateExtension($lastSegment);

        return $parts['name'];
    }

    /**
     * Get the directory path from the current path.
     *
     * @return string Directory path or empty string
     */
    public function getDirectoryPath(): string
    {
        $path = $this->toString();
        $dirname = pathinfo($path, PATHINFO_DIRNAME);

        return $dirname === '.' ? '' : $dirname;
    }

    /**
     * Ensure path has trailing slash.
     *
     * @return self Current instance for chaining
     */
    public function ensureTrailing(): self
    {
        if (! empty($this->segments)) {
            $lastIndex = count($this->segments) - 1;
            $this->segments[$lastIndex] = rtrim($this->segments[$lastIndex], '/').'/';
        }

        return $this;
    }

    /**
     * Remove trailing slash from path.
     *
     * @return self Current instance for chaining
     */
    public function removeTrailing(): self
    {
        if (! empty($this->segments)) {
            $lastIndex = count($this->segments) - 1;
            $this->segments[$lastIndex] = rtrim($this->segments[$lastIndex], '/');
        }

        return $this;
    }

    /**
     * Validate the current path for security.
     *
     * @return self Current instance for chaining
     * @throws UnsafePathException If path is unsafe
     */
    public function validate(): self
    {
        $path = $this->toString();
        if (! self::isSafe($path)) {
            throw UnsafePathException::create($path);
        }

        return $this;
    }

    /**
     * Check if path must exist on given disk.
     *
     * @param  string  $disk  Storage disk name
     * @return self Current instance for chaining
     * @throws PathNotFoundException If path doesn't exist
     */
    public function mustExist(string $disk = 'local'): self
    {
        $path = $this->toString();
        if (! Storage::disk($disk)->exists($path)) {
            throw PathNotFoundException::create($path, $disk);
        }

        return $this;
    }

    /**
     * Check if path must not exist on given disk.
     *
     * @param  string  $disk  Storage disk name
     * @return self Current instance for chaining
     * @throws PathAlreadyExistsException If path already exists
     */
    public function mustNotExist(string $disk = 'local'): self
    {
        $path = $this->toString();
        if (Storage::disk($disk)->exists($path)) {
            throw PathAlreadyExistsException::create($path, $disk);
        }

        return $this;
    }

    /**
     * Check if the current path exists on storage.
     *
     * @param  string  $disk  Storage disk name
     * @return bool True if path exists
     */
    public function exists(string $disk = 'local'): bool
    {
        return Storage::disk($disk)->exists($this->toString());
    }

    /**
     * Get file size from storage.
     *
     * @param  string  $disk  Storage disk name
     * @return int File size in bytes
     */
    public function size(string $disk = 'local'): int
    {
        return Storage::disk($disk)->size($this->toString());
    }

    /**
     * Get formatted file size from storage.
     *
     * @param  string  $disk  Storage disk name
     * @param  int  $precision  Number of decimal places
     * @return string Formatted file size
     * @throws \InvalidArgumentException If file size is negative
     */
    public function getSizeFormatted(string $disk = 'local', int $precision = 3): string
    {
        return FileSize::format($this->size($disk), $precision);
    }

    /**
     * Get file size in kilobytes (binary).
     *
     * @param  string  $disk  Storage disk name
     * @return float File size in KB
     * @throws \InvalidArgumentException If file size is negative
     */
    public function getSizeInKB(string $disk = 'local'): float
    {
        return FileSize::toKB($this->size($disk));
    }

    /**
     * Get file size in megabytes (binary).
     *
     * @param  string  $disk  Storage disk name
     * @return float File size in MB
     * @throws \InvalidArgumentException If file size is negative
     */
    public function getSizeInMB(string $disk = 'local'): float
    {
        return FileSize::toMB($this->size($disk));
    }

    /**
     * Get file size in gigabytes (binary).
     *
     * @param  string  $disk  Storage disk name
     * @return float File size in GB
     * @throws \InvalidArgumentException If file size is negative
     */
    public function getSizeInGB(string $disk = 'local'): float
    {
        return FileSize::toGB($this->size($disk));
    }

    /**
     * Get file size in kilobytes (decimal).
     *
     * @param  string  $disk  Storage disk name
     * @return float File size in KB (decimal)
     * @throws \InvalidArgumentException If file size is negative
     */
    public function getSizeInKBDecimal(string $disk = 'local'): float
    {
        return FileSize::toKBDecimal($this->size($disk));
    }

    /**
     * Get file size in megabytes (decimal).
     *
     * @param  string  $disk  Storage disk name
     * @return float File size in MB (decimal)
     * @throws \InvalidArgumentException If file size is negative
     */
    public function getSizeInMBDecimal(string $disk = 'local'): float
    {
        return FileSize::toMBDecimal($this->size($disk));
    }

    /**
     * Get file size in gigabytes (decimal).
     *
     * @param  string  $disk  Storage disk name
     * @return float File size in GB (decimal)
     * @throws \InvalidArgumentException If file size is negative
     */
    public function getSizeInGBDecimal(string $disk = 'local'): float
    {
        return FileSize::toGBDecimal($this->size($disk));
    }

    /**
     * Get public URL for the path.
     *
     * @param  string  $disk  Storage disk name
     * @return string Public URL
     */
    public function url(string $disk = 'local'): string
    {
        /** @var \Illuminate\Contracts\Filesystem\Filesystem&\Illuminate\Contracts\Filesystem\Cloud $storage */
        $storage = Storage::disk($disk);

        return $storage->url($this->toString());
    }

    /**
     * Delete file at the current path.
     *
     * @param  string  $disk  Storage disk name
     * @return bool True if deletion was successful
     */
    public function delete(string $disk = 'local'): bool
    {
        return Storage::disk($disk)->delete($this->toString());
    }

    /**
     * Get debugging information about the current path.
     *
     * @return array Debug information
     */
    public function debug(): array
    {
        $path = $this->toString();

        return [
            'segments' => $this->segments,
            'final_path' => $path,
            'is_safe' => self::isSafe($path),
            'extension' => $this->getExtension(),
            'filename' => $this->getFilename(),
            'filename_without_ext' => $this->getFilenameWithoutExtension(),
            'directory' => $this->getDirectoryPath(),
        ];
    }

    /**
     * Convert PathBuilder to string.
     *
     * @return string Final path as string
     */
    public function toString(): string
    {
        return self::normalize(implode('/', array_filter($this->segments, fn ($s) => $s !== '')));
    }

    /**
     * Convert PathBuilder to string (alias for toString).
     *
     * @return string Final path as string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    // Static utility methods (backward compatibility)

    /**
     * Build a path from array of segments.
     *
     * @param  array<string>  $segments  Path segments
     * @return string Built path with proper separators
     */
    public static function build(array $segments): string
    {
        return collect($segments)
            ->filter()
            ->map(fn (string $segment) => Sanitizer::trimSlashes($segment))
            ->filter()
            ->implode('/');
    }

    /**
     * Join path segments with forward slashes.
     *
     * @param  string  ...$segments  Variable number of path segments
     * @return string Joined path
     */
    public static function join(string ...$segments): string
    {
        return self::build($segments);
    }

    /**
     * Create a secure hash-based directory name.
     *
     * @param  string  $input  Input to hash
     * @param  string  $algorithm  Hash algorithm (default: md5)
     * @return string Hashed directory name
     */
    public static function createSecureDirectoryName(string $input, string $algorithm = 'md5'): string
    {
        return hash($algorithm, $input);
    }

    /**
     * Extract filename from a path using Laravel's File facade.
     *
     * @param  string  $path  Full path
     * @return string Filename only
     */
    public static function extractFilename(string $path): string
    {
        return File::basename($path);
    }

    /**
     * Get the last segment from a path (similar to afterLast).
     *
     * @param  string  $path  Full path
     * @return string Last path segment
     */
    public static function getLastSegment(string $path): string
    {
        return Str::of($path)->afterLast('/')->toString();
    }

    /**
     * Build relative path from absolute path and base path.
     *
     * @param  string  $absolutePath  Absolute path
     * @param  string  $basePath  Base path to remove
     * @return string Relative path
     */
    public static function buildRelativePath(string $absolutePath, string $basePath): string
    {
        $basePath = Sanitizer::trimSlashes($basePath);
        $absolutePath = Sanitizer::trimSlashes($absolutePath);

        if (str_starts_with($absolutePath, $basePath)) {
            return ltrim(substr($absolutePath, strlen($basePath)), '/');
        }

        return $absolutePath;
    }

    /**
     * Get file extension from path.
     *
     * @param  string  $path  File path
     * @return string File extension
     */
    public static function getFileExtension(string $path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION) ?: '';
    }

    /**
     * Get filename without extension from path.
     *
     * @param  string  $path  File path
     * @return string Filename without extension
     */
    public static function extractFilenameWithoutExtension(string $path): string
    {
        return pathinfo($path, PATHINFO_FILENAME) ?: '';
    }

    /**
     * Get directory path from full path.
     *
     * @param  string  $path  Full file path
     * @return string Directory path
     */
    public static function getDirectoryPathStatic(string $path): string
    {
        $dirname = pathinfo($path, PATHINFO_DIRNAME);

        return $dirname === '.' ? '' : $dirname;
    }

    /**
     * Normalize a path by removing duplicate slashes and ensuring proper format.
     *
     * @param  string  $path  Path to normalize
     * @return string Normalized path
     */
    public static function normalize(string $path): string
    {
        if (empty($path)) {
            return '';
        }

        // Remove duplicate slashes and normalize
        $normalized = preg_replace('#/+#', '/', $path);
        if ($normalized === null) {
            return '';
        }

        // Remove trailing slash unless it's the root
        return $normalized === '/' ? $normalized : rtrim($normalized, '/');
    }

    /**
     * Check if a path is safe (no directory traversal).
     *
     * @param  string  $path  Path to check
     * @return bool True if path is safe
     */
    public static function isSafe(string $path): bool
    {
        return ! Str::contains($path, ['../', '..\\']) && ! str_contains($path, '..');
    }
}
