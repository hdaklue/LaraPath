<?php

declare(strict_types=1);

use Hdaklue\PathBuilder\Enums\SanitizationStrategy;
use Hdaklue\PathBuilder\Exceptions\UnsafePathException;
use Hdaklue\PathBuilder\Facades\LaraPath;
use Hdaklue\PathBuilder\PathBuilder;
use Illuminate\Support\Facades\Storage;

describe('Laravel Integration', function () {
    describe('Service Provider', function () {
        it('registers PathBuilder in container', function () {
            expect(app()->bound('larapath'))->toBeTrue();
        });

        it('resolves PathBuilder from container', function () {
            $pathBuilder = app('larapath');

            expect($pathBuilder)->toBeInstanceOf(PathBuilder::class);
        });
    });

    describe('LaraPath Facade', function () {
        it('creates path builder through facade', function () {
            $builder = LaraPath::base('uploads');

            expect($builder)->toBeInstanceOf(PathBuilder::class)
                ->and($builder->toString())->toBe('uploads');
        });

        it('builds paths through facade', function () {
            $path = LaraPath::build(['uploads', 'images', 'photo.jpg']);

            expect($path)->toBe('uploads/images/photo.jpg');
        });

        it('joins paths through facade', function () {
            $path = LaraPath::join('uploads', 'videos', 'clip.mp4');

            expect($path)->toBe('uploads/videos/clip.mp4');
        });

        it('normalizes paths through facade', function () {
            $normalized = LaraPath::normalize('uploads//images///photo.jpg');

            expect($normalized)->toBe('uploads/images/photo.jpg');
        });

        it('checks path safety through facade', function () {
            expect(LaraPath::isSafe('uploads/safe/path.jpg'))->toBeTrue()
                ->and(LaraPath::isSafe('../unsafe/path'))->toBeFalse();
        });
    });
});

describe('Laravel Storage Integration', function () {
    beforeEach(function () {
        Storage::fake('local');
        Storage::fake('public');
    });

    it('checks file existence on storage disk', function () {
        Storage::disk('local')->put('test/file.txt', 'content');

        $exists = PathBuilder::base('test/file.txt')->exists('local');

        expect($exists)->toBeTrue();
    });

    it('gets file size from storage disk', function () {
        Storage::disk('local')->put('test/file.txt', 'test content');

        $size = PathBuilder::base('test/file.txt')->size('local');

        expect($size)->toBe(strlen('test content'));
    });

    it('deletes file from storage disk', function () {
        Storage::disk('local')->put('test/file.txt', 'content');

        $deleted = PathBuilder::base('test/file.txt')->delete('local');

        expect($deleted)->toBeTrue()
            ->and(Storage::disk('local')->exists('test/file.txt'))->toBeFalse();
    });

    it('validates path must exist', function () {
        Storage::disk('local')->put('existing/file.txt', 'content');

        $result = PathBuilder::base('existing/file.txt')->mustExist('local');

        expect($result)->toBeInstanceOf(PathBuilder::class);
    });

    it('throws exception when path must exist but doesnt', function () {
        expect(fn () => PathBuilder::base('missing/file.txt')->mustExist('local'))
            ->toThrow(\Hdaklue\PathBuilder\Exceptions\PathNotFoundException::class);
    });

    it('validates path must not exist', function () {
        $result = PathBuilder::base('new/file.txt')->mustNotExist('local');

        expect($result)->toBeInstanceOf(PathBuilder::class);
    });

    it('throws exception when path must not exist but does', function () {
        Storage::disk('local')->put('existing/file.txt', 'content');

        expect(fn () => PathBuilder::base('existing/file.txt')->mustNotExist('local'))
            ->toThrow(\Hdaklue\PathBuilder\Exceptions\PathAlreadyExistsException::class);
    });
});

describe('Real-world Laravel Usage Scenarios', function () {
    beforeEach(function () {
        Storage::fake('public');
    });

    it('creates user upload path with hashed directory', function () {
        $userId = 'user@example.com';

        $path = PathBuilder::base('uploads')
            ->add('users')
            ->addHashedDir($userId)
            ->add('profile-photo.jpg')
            ->toString();

        expect($path)->toBe('uploads/users/'.md5($userId).'/profile-photo.jpg');
    });

    it('creates timestamped cache path', function () {
        $timestamp = time();

        $path = PathBuilder::base('cache')
            ->add('generated', SanitizationStrategy::SLUG)
            ->addTimestampedDir()
            ->add('report.pdf')
            ->toString();

        expect($path)->toStartWith('cache/generated/')
            ->and($path)->toContain((string) $timestamp)
            ->and($path)->toEndWith('/report.pdf');
    });

    it('sanitizes user input for safe file paths', function () {
        $userInput = 'My Awesome File!!!';

        $path = PathBuilder::base('uploads')
            ->add($userInput, SanitizationStrategy::SLUG)
            ->add('document.txt')
            ->toString();

        expect($path)->toBe('uploads/my-awesome-file/document.txt');
    });

    it('builds relative path from absolute storage path', function () {
        $absolutePath = '/var/www/storage/app/uploads/image.jpg';
        $basePath = '/var/www/storage/app';

        $relativePath = PathBuilder::buildRelativePath($absolutePath, $basePath);

        expect($relativePath)->toBe('uploads/image.jpg');
    });

    it('validates and rejects unsafe file upload paths', function () {
        expect(fn () => PathBuilder::base('uploads')
            ->add('../../../etc/passwd')
            ->validate())
            ->toThrow(UnsafePathException::class);
    });

    it('handles complex nested directory structure', function () {
        $path = PathBuilder::base('storage')
            ->add('app')
            ->add('tenant-'.time())
            ->add('uploads')
            ->addHashedDir('sensitive-data')
            ->add('Document File', SanitizationStrategy::SLUG)
            ->add('final.pdf')
            ->toString();

        expect($path)->toStartWith('storage/app/tenant-')
            ->and($path)->toContain('/uploads/')
            ->and($path)->toContain('/document-file/')
            ->and($path)->toEndWith('/final.pdf')
            ->and(PathBuilder::isSafe($path))->toBeTrue();
    });
});
