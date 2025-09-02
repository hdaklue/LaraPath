<?php

declare(strict_types=1);

namespace Hdaklue\PathBuilder\Utilities;

use Illuminate\Support\Number;

final class FileSize
{
    public static function fromMB(float $megabytes): int
    {
        self::validateInput($megabytes, 'fromMB');

        return (int) ($megabytes * 1024 * 1024);
    }

    public static function fromGB(float $gigabytes): int
    {
        self::validateInput($gigabytes, 'fromGB');

        return (int) ($gigabytes * 1024 * 1024 * 1024);
    }

    public static function fromKB(float $kilobytes): int
    {
        self::validateInput($kilobytes, 'fromKB');

        return (int) ($kilobytes * 1024);
    }

    private static function validateInput(float|int $value, string $methodName): void
    {
        if ($value < 0) {
            throw new \InvalidArgumentException("Value cannot be negative in {$methodName}");
        }
    }

    public static function toMB(int $bytes): float
    {
        self::validateInput($bytes, 'toMB');

        return $bytes / (1024 * 1024);
    }

    public static function toGB(int $bytes): float
    {
        self::validateInput($bytes, 'toGB');

        return $bytes / (1024 * 1024 * 1024);
    }

    public static function toKB(int $bytes): float
    {
        self::validateInput($bytes, 'toKB');

        return $bytes / 1024;
    }

    // Decimal (base 10) conversions - matches Finder/Windows Explorer
    public static function toMBDecimal(int $bytes): float
    {
        self::validateInput($bytes, 'toMBDecimal');

        return $bytes / (1000 * 1000);
    }

    public static function toGBDecimal(int $bytes): float
    {
        self::validateInput($bytes, 'toGBDecimal');

        return $bytes / (1000 * 1000 * 1000);

    }

    public static function toKBDecimal(int $bytes): float
    {
        self::validateInput($bytes, 'toKBDecimal');

        return $bytes / 1000;
    }

    public static function format(int $bytes, int $precision = 3): string
    {
        self::validateInput($bytes, 'format');

        return Number::fileSize($bytes, $precision);

    }
}
