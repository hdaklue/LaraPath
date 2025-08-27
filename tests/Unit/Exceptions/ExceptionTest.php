<?php

declare(strict_types=1);

use Hdaklue\PathBuilder\Exceptions\InvalidSanitizationStrategyException;
use Hdaklue\PathBuilder\Exceptions\PathAlreadyExistsException;
use Hdaklue\PathBuilder\Exceptions\PathNotFoundException;
use Hdaklue\PathBuilder\Exceptions\UnsafePathException;

describe('UnsafePathException', function () {
    it('creates descriptive message', function () {
        $exception = UnsafePathException::create('../etc/passwd');

        expect($exception->getMessage())->toContain('Unsafe path detected: ../etc/passwd');
    });
});

describe('PathNotFoundException', function () {
    it('creates descriptive message with custom disk', function () {
        $exception = PathNotFoundException::create('missing/file.txt', 'public');

        expect($exception->getMessage())->toContain('Path does not exist: missing/file.txt on disk: public');
    });

    it('defaults to local disk', function () {
        $exception = PathNotFoundException::create('missing/file.txt');

        expect($exception->getMessage())->toContain('on disk: local');
    });
});

describe('PathAlreadyExistsException', function () {
    it('creates descriptive message with custom disk', function () {
        $exception = PathAlreadyExistsException::create('existing/file.txt', 's3');

        expect($exception->getMessage())->toContain('Path already exists: existing/file.txt on disk: s3');
    });

    it('defaults to local disk', function () {
        $exception = PathAlreadyExistsException::create('existing/file.txt');

        expect($exception->getMessage())->toContain('on disk: local');
    });
});

describe('InvalidSanitizationStrategyException', function () {
    it('creates descriptive message', function () {
        $exception = InvalidSanitizationStrategyException::create('NonExistentStrategy');

        expect($exception->getMessage())->toContain("Strategy class NonExistentStrategy not found or doesn't implement apply() method");
    });
});
