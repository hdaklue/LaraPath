<?php

declare(strict_types=1);

use Hdaklue\PathBuilder\Enums\SanitizationStrategy;
use Hdaklue\PathBuilder\Exceptions\UnsafePathException;
use Hdaklue\PathBuilder\PathBuilder;

describe('PathBuilder Basic Operations', function () {
    it('creates path builder instance from base', function () {
        $builder = PathBuilder::base('uploads');

        expect($builder->toString())->toBe('uploads');
    });

    it('appends path segments with add method', function () {
        $path = PathBuilder::base('uploads')
            ->add('images')
            ->add('avatar.jpg')
            ->toString();

        expect($path)->toBe('uploads/images/avatar.jpg');
    });

    it('automatically trims slashes', function () {
        $path = PathBuilder::base('/uploads/')
            ->add('/images/')
            ->add('/avatar.jpg/')
            ->toString();

        expect($path)->toBe('uploads/images/avatar.jpg');
    });
});

describe('PathBuilder Sanitization Strategies', function () {
    it('applies hashed sanitization strategy', function () {
        $path = PathBuilder::base('uploads')
            ->add('user@email.com', SanitizationStrategy::HASHED)
            ->toString();

        expect($path)->toBe('uploads/'.md5('user@email.com'));
    });

    it('applies slug sanitization strategy', function () {
        $path = PathBuilder::base('uploads')
            ->add('My Amazing File!', SanitizationStrategy::SLUG)
            ->toString();

        expect($path)->toBe('uploads/my-amazing-file');
    });

    it('applies snake sanitization strategy', function () {
        $path = PathBuilder::base('uploads')
            ->add('CamelCase Name', SanitizationStrategy::SNAKE)
            ->toString();

        expect($path)->toBe('uploads/camel_case_name');
    });

    it('applies timestamp sanitization strategy', function () {
        $timestamp = time();

        $path = PathBuilder::base('temp')
            ->add('session', SanitizationStrategy::TIMESTAMP)
            ->toString();

        expect($path)->toStartWith('temp/session_')
            ->and($path)->toContain((string) $timestamp);
    });
});

describe('PathBuilder Immutability', function () {
    it('maintains immutable operations', function () {
        $base = PathBuilder::base('uploads');
        $images = $base->add('images');
        $videos = $base->add('videos');

        expect($base->toString())->toBe('uploads')
            ->and($images->toString())->toBe('uploads/images')
            ->and($videos->toString())->toBe('uploads/videos');
    });
});

describe('PathBuilder File Operations', function () {
    it('gets file extension', function () {
        $builder = PathBuilder::base('files/video.mp4');

        expect($builder->getExtension())->toBe('mp4');
    });

    it('gets filename', function () {
        $builder = PathBuilder::base('files/video.mp4');

        expect($builder->getFilename())->toBe('video.mp4');
    });

    it('gets filename without extension', function () {
        $builder = PathBuilder::base('files/video.mp4');

        expect($builder->getFilenameWithoutExtension())->toBe('video');
    });

    it('gets directory path', function () {
        $builder = PathBuilder::base('files/video.mp4');

        expect($builder->getDirectoryPath())->toBe('files');
    });

    it('replaces file extension', function () {
        $newPath = PathBuilder::base('files/video.mp4')
            ->replaceExtension('webm')
            ->toString();

        expect($newPath)->toBe('files/video.webm');
    });
});

describe('PathBuilder Directory Operations', function () {
    it('adds timestamped directory', function () {
        $timestamp = time();
        $path = PathBuilder::base('uploads')
            ->addTimestampedDir()
            ->toString();

        expect($path)->toStartWith('uploads/')
            ->and($path)->toContain((string) $timestamp);
    });

    it('adds hashed directory', function () {
        $path = PathBuilder::base('uploads')
            ->addHashedDir('user123')
            ->toString();

        expect($path)->toBe('uploads/'.md5('user123'));
    });
});

describe('PathBuilder Static Methods', function () {
    it('normalizes by removing duplicate slashes', function () {
        $normalized = PathBuilder::normalize('uploads//images///avatar.jpg');

        expect($normalized)->toBe('uploads/images/avatar.jpg');
    });

    it('detects directory traversal in isSafe', function () {
        expect(PathBuilder::isSafe('../etc/passwd'))->toBeFalse()
            ->and(PathBuilder::isSafe('uploads/../../../etc/passwd'))->toBeFalse()
            ->and(PathBuilder::isSafe('uploads/images/avatar.jpg'))->toBeTrue();
    });

    it('throws exception for unsafe paths in validate', function () {
        expect(fn () => PathBuilder::base('uploads')
            ->add('../../../etc/passwd')
            ->validate())
            ->toThrow(UnsafePathException::class, 'Unsafe path detected');
    });

    it('builds path from array with build method', function () {
        $path = PathBuilder::build(['uploads', 'images', 'avatar.jpg']);

        expect($path)->toBe('uploads/images/avatar.jpg');
    });

    it('joins path segments with join method', function () {
        $path = PathBuilder::join('uploads', 'images', 'avatar.jpg');

        expect($path)->toBe('uploads/images/avatar.jpg');
    });

    it('builds relative path', function () {
        $relative = PathBuilder::buildRelativePath('/var/www/uploads/image.jpg', '/var/www');

        expect($relative)->toBe('uploads/image.jpg');
    });
});

describe('PathBuilder Debug and Magic Methods', function () {
    it('returns path information in debug', function () {
        $debug = PathBuilder::base('files/video.mp4')->debug();

        expect($debug)->toBeArray()
            ->toHaveKeys(['segments', 'final_path', 'is_safe', 'extension', 'filename'])
            ->and($debug['final_path'])->toBe('files/video.mp4')
            ->and($debug['extension'])->toBe('mp4')
            ->and($debug['filename'])->toBe('video.mp4')
            ->and($debug['is_safe'])->toBeTrue();
    });

    it('converts to string with magic method', function () {
        $builder = PathBuilder::base('uploads')->add('images');

        expect((string) $builder)->toBe('uploads/images');
    });
});
