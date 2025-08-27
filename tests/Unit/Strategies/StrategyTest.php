<?php

declare(strict_types=1);

use Hdaklue\PathBuilder\Strategies\HashedStrategy;
use Hdaklue\PathBuilder\Strategies\SlugStrategy;
use Hdaklue\PathBuilder\Strategies\SnakeStrategy;
use Hdaklue\PathBuilder\Strategies\TimestampStrategy;

describe('HashedStrategy', function () {
    it('creates md5 hash', function () {
        $result = HashedStrategy::apply('test@example.com');

        expect($result)->toBe(md5('test@example.com'))
            ->and(strlen($result))->toBe(32); // MD5 is 32 characters
    });

    it('works with different algorithm', function () {
        $result = HashedStrategy::apply('test', 'sha256');

        expect($result)->toBe(hash('sha256', 'test'))
            ->and(strlen($result))->toBe(64); // SHA256 is 64 characters
    });
});

describe('SlugStrategy', function () {
    it('creates url friendly slug', function () {
        $result = SlugStrategy::apply('My Amazing File!');

        expect($result)->toBe('my-amazing-file');
    });

    it('handles special characters', function () {
        $result = SlugStrategy::apply('File with @#$%^&*() characters');

        expect($result)->toBe('file-with-characters');
    });
});

describe('SnakeStrategy', function () {
    it('converts to snake case', function () {
        $result = SnakeStrategy::apply('CamelCaseName');

        expect($result)->toBe('camel_case_name');
    });

    it('handles spaces', function () {
        $result = SnakeStrategy::apply('Multiple Word String');

        expect($result)->toBe('multiple_word_string');
    });
});

describe('TimestampStrategy', function () {
    it('appends timestamp', function () {
        $input = 'session';
        $timestamp = time();

        $result = TimestampStrategy::apply($input);

        expect($result)->toStartWith($input.'_')
            ->and($result)->toContain((string) $timestamp);
    });

    it('preserves original input', function () {
        $input = 'my-file';
        $result = TimestampStrategy::apply($input);

        expect($result)->toStartWith($input.'_');
    });
});
