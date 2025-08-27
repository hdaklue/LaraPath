<?php

declare(strict_types=1);

namespace Hdaklue\PathBuilder\Tests\Unit\Exceptions;

use Hdaklue\PathBuilder\Exceptions\InvalidSanitizationStrategyException;
use Hdaklue\PathBuilder\Exceptions\PathAlreadyExistsException;
use Hdaklue\PathBuilder\Exceptions\PathNotFoundException;
use Hdaklue\PathBuilder\Exceptions\UnsafePathException;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    public function test_unsafe_path_exception_creates_descriptive_message(): void
    {
        $exception = UnsafePathException::create('../etc/passwd');

        $this->assertStringContainsString('Unsafe path detected: ../etc/passwd', $exception->getMessage());
    }

    public function test_path_not_found_exception_creates_descriptive_message(): void
    {
        $exception = PathNotFoundException::create('missing/file.txt', 'public');

        $this->assertStringContainsString('Path does not exist: missing/file.txt on disk: public', $exception->getMessage());
    }

    public function test_path_not_found_exception_defaults_to_local_disk(): void
    {
        $exception = PathNotFoundException::create('missing/file.txt');

        $this->assertStringContainsString('on disk: local', $exception->getMessage());
    }

    public function test_path_already_exists_exception_creates_descriptive_message(): void
    {
        $exception = PathAlreadyExistsException::create('existing/file.txt', 's3');

        $this->assertStringContainsString('Path already exists: existing/file.txt on disk: s3', $exception->getMessage());
    }

    public function test_path_already_exists_exception_defaults_to_local_disk(): void
    {
        $exception = PathAlreadyExistsException::create('existing/file.txt');

        $this->assertStringContainsString('on disk: local', $exception->getMessage());
    }

    public function test_invalid_sanitization_strategy_exception_creates_descriptive_message(): void
    {
        $exception = InvalidSanitizationStrategyException::create('NonExistentStrategy');

        $this->assertStringContainsString("Strategy class NonExistentStrategy not found or doesn't implement apply() method", $exception->getMessage());
    }
}
