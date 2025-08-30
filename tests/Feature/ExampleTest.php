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

describe('Sanitization Strategy Validation', function () {
    it('throws exception for non-existent strategy class', function () {
        expect(fn () => PathBuilder::base('uploads')
            ->add('test', 'NonExistentStrategy'))
            ->toThrow(\Hdaklue\PathBuilder\Exceptions\InvalidSanitizationStrategyException::class);
    });

    it('throws exception for class that does not implement contract', function () {
        expect(fn () => PathBuilder::base('uploads')
            ->add('test', \stdClass::class))
            ->toThrow(\Hdaklue\PathBuilder\Exceptions\InvalidSanitizationStrategyException::class, 'must implement SanitizationStrategyContract interface');
    });

    it('accepts valid strategy enum', function () {
        $path = PathBuilder::base('uploads')
            ->add('test', SanitizationStrategy::SLUG)
            ->toString();

        expect($path)->toBe('uploads/test');
    });

    it('accepts valid strategy classes', function () {
        $path = PathBuilder::base('uploads')
            ->add('test', \Hdaklue\PathBuilder\Strategies\SlugStrategy::class)
            ->toString();

        expect($path)->toBe('uploads/test');
    });
});

describe('Core PathBuilder Functionality', function () {
    it('clones instances correctly in add method', function () {
        $original = PathBuilder::base('uploads');
        $modified = $original->add('test');

        expect($original->toString())->toBe('uploads')
            ->and($modified->toString())->toBe('uploads/test')
            ->and($original)->not->toBe($modified);
    });

    it('modifies instance in addFile method', function () {
        $builder = PathBuilder::base('uploads');
        $result = $builder->addFile('test.txt');

        expect($result)->toBe($builder)
            ->and($builder->toString())->toBe('uploads/test.txt');
    });

    it('gets extension correctly', function () {
        $path = PathBuilder::base('uploads')
            ->addFile('document.pdf');

        expect($path->getExtension())->toBe('pdf');
    });

    it('gets filename correctly', function () {
        $path = PathBuilder::base('uploads')
            ->addFile('document.pdf');

        expect($path->getFilename())->toBe('document.pdf');
    });

    it('gets filename without extension correctly', function () {
        $path = PathBuilder::base('uploads')
            ->addFile('document.pdf');

        expect($path->getFilenameWithoutExtension())->toBe('document');
    });

    it('gets directory path correctly', function () {
        $path = PathBuilder::base('uploads')
            ->add('files')
            ->addFile('document.pdf');

        expect($path->getDirectoryPath())->toBe('uploads/files');
    });

    it('replaces extension correctly', function () {
        $original = PathBuilder::base('uploads')
            ->addFile('document.pdf');

        $modified = $original->replaceExtension('txt');

        expect($original->toString())->toBe('uploads/document.pdf')
            ->and($modified->toString())->toBe('uploads/document.txt')
            ->and($original)->not->toBe($modified);
    });

    it('handles trailing slash operations', function () {
        $path = PathBuilder::base('uploads')
            ->add('folder')
            ->ensureTrailing();

        // Note: normalize() removes trailing slashes, so ensureTrailing doesn't show in toString()
        expect($path->toString())->toBe('uploads/folder');

        // But the segments should have the trailing slash
        $debug = $path->debug();
        expect($debug['segments'])->toContain('folder/');

        $path->removeTrailing();
        expect($path->toString())->toBe('uploads/folder');
    });

    it('provides debug information', function () {
        $path = PathBuilder::base('uploads')
            ->add('files')
            ->addFile('document.pdf');

        $debug = $path->debug();

        expect($debug)->toHaveKey('segments')
            ->and($debug)->toHaveKey('final_path')
            ->and($debug)->toHaveKey('is_safe')
            ->and($debug)->toHaveKey('extension')
            ->and($debug)->toHaveKey('filename')
            ->and($debug)->toHaveKey('filename_without_ext')
            ->and($debug)->toHaveKey('directory')
            ->and($debug['extension'])->toBe('pdf')
            ->and($debug['is_safe'])->toBeTrue();
    });
});

describe('Error Handling and Edge Cases', function () {
    it('handles empty segments correctly', function () {
        $path = PathBuilder::base('')
            ->add('')
            ->add('uploads')
            ->toString();

        expect($path)->toBe('uploads');
    });

    it('normalizes paths with multiple slashes', function () {
        $normalized = PathBuilder::normalize('uploads///files//document.pdf');

        expect($normalized)->toBe('uploads/files/document.pdf');
    });

    it('handles root path normalization', function () {
        $normalized = PathBuilder::normalize('/');

        expect($normalized)->toBe('/');
    });

    it('returns empty string for empty path normalization', function () {
        $normalized = PathBuilder::normalize('');

        expect($normalized)->toBe('');
    });

    it('detects unsafe paths with different patterns', function () {
        expect(PathBuilder::isSafe('uploads/../../../etc/passwd'))->toBeFalse()
            ->and(PathBuilder::isSafe('uploads/..\\\\etc\\\\passwd'))->toBeFalse()
            ->and(PathBuilder::isSafe('uploads/file..name'))->toBeFalse()
            ->and(PathBuilder::isSafe('uploads/safe/path'))->toBeTrue();
    });

    it('handles extension replacement on paths without extensions', function () {
        $path = PathBuilder::base('uploads')
            ->add('folder')
            ->replaceExtension('txt');

        expect($path->toString())->toBe('uploads/folder');
    });

    it('handles extension operations on empty paths', function () {
        $path = PathBuilder::base('');

        expect($path->getExtension())->toBe('')
            ->and($path->getFilename())->toBe('')
            ->and($path->getFilenameWithoutExtension())->toBe('')
            ->and($path->getDirectoryPath())->toBe('');
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
