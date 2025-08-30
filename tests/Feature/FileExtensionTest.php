<?php

declare(strict_types=1);

use Hdaklue\PathBuilder\Enums\SanitizationStrategy;
use Hdaklue\PathBuilder\Exceptions\UnsafePathException;
use Hdaklue\PathBuilder\PathBuilder;

describe('File Extension Preservation', function () {
    describe('addFile method with sanitization strategies', function () {
        it('preserves file extensions with SLUG strategy', function () {
            $path = PathBuilder::base('uploads')
                ->addFile('test.jpg', SanitizationStrategy::SLUG)
                ->toString();

            expect($path)->toBe('uploads/test.jpg');
        });

        it('preserves file extensions with HASHED strategy', function () {
            $path = PathBuilder::base('uploads')
                ->addFile('test.jpg', SanitizationStrategy::HASHED)
                ->toString();

            // Should be hash of 'test' + '.jpg'
            expect($path)->toBe('uploads/098f6bcd4621d373cade4e832627b4f6.jpg');
        });

        it('preserves file extensions with SNAKE strategy', function () {
            $path = PathBuilder::base('uploads')
                ->addFile('test.jpg', SanitizationStrategy::SNAKE)
                ->toString();

            expect($path)->toBe('uploads/test.jpg');
        });

        it('preserves file extensions with TIMESTAMP strategy', function () {
            $path = PathBuilder::base('uploads')
                ->addFile('test.jpg', SanitizationStrategy::TIMESTAMP)
                ->toString();

            expect($path)->toStartWith('uploads/test_')
                ->and($path)->toEndWith('.jpg')
                ->and($path)->toMatch('/^uploads\/test_\d+\.jpg$/');
        });

        it('handles complex filenames while preserving extensions', function () {
            $path = PathBuilder::base('uploads')
                ->addFile('My Complex File Name!@#.pdf', SanitizationStrategy::SLUG)
                ->toString();

            expect($path)->toBe('uploads/my-complex-file-name.pdf');
        });

        it('handles filenames without extensions', function () {
            $path = PathBuilder::base('uploads')
                ->addFile('README', SanitizationStrategy::SLUG)
                ->toString();

            expect($path)->toBe('uploads/readme');
        });

        it('preserves only the last extension for multiple dots', function () {
            $path = PathBuilder::base('uploads')
                ->addFile('archive.tar.gz', SanitizationStrategy::SLUG)
                ->toString();

            expect($path)->toBe('uploads/archivetar.gz');
        });

        it('handles hidden files (starting with dot)', function () {
            $path = PathBuilder::base('uploads')
                ->addFile('.htaccess', SanitizationStrategy::SLUG)
                ->toString();

            expect($path)->toBe('uploads/htaccess');
        });

        it('handles files ending with dot', function () {
            $path = PathBuilder::base('uploads')
                ->addFile('file.', SanitizationStrategy::SLUG)
                ->toString();

            expect($path)->toBe('uploads/file');
        });
    });

    describe('Sanitization consistency', function () {
        it('preserves extensions in both add() and addFile() methods', function () {
            $addPath = PathBuilder::base('uploads')
                ->add('backup.folder', SanitizationStrategy::SLUG)
                ->toString();

            $addFilePath = PathBuilder::base('uploads')
                ->addFile('backup.folder', SanitizationStrategy::SLUG)
                ->toString();

            // Both should preserve the extension
            expect($addPath)->toBe('uploads/backup.folder')
                ->and($addFilePath)->toBe('uploads/backup.folder');
        });
    });

    describe('Mixed usage scenarios', function () {
        it('works correctly in complex path building', function () {
            $path = PathBuilder::base('storage')
                ->add('app')
                ->add('User Files', SanitizationStrategy::SLUG)
                ->addFile('Document Name.docx', SanitizationStrategy::SLUG)
                ->toString();

            expect($path)->toBe('storage/app/user-files/document-name.docx');
        });

        it('works with hashed directories and files', function () {
            $userEmail = 'user@example.com';
            
            $path = PathBuilder::base('uploads')
                ->add('users')
                ->add($userEmail, SanitizationStrategy::HASHED)
                ->addFile('Profile Photo.jpg', SanitizationStrategy::SLUG)
                ->toString();

            // HASHED strategy treats .com as extension, so hashes only "user@example" 
            $expectedHash = hash('md5', 'user@example');
            expect($path)->toBe("uploads/users/{$expectedHash}.com/profile-photo.jpg");
        });

        it('works with timestamped files', function () {
            $userFile = 'Report.xlsx';
            
            $path = PathBuilder::base('reports')
                ->add('monthly')
                ->addFile($userFile, SanitizationStrategy::TIMESTAMP)
                ->toString();

            expect($path)->toStartWith('reports/monthly/Report_')
                ->and($path)->toEndWith('.xlsx')
                ->and($path)->toMatch('/^reports\/monthly\/Report_\d+\.xlsx$/');
        });

        it('works with snake_case filenames', function () {
            $path = PathBuilder::base('documents')
                ->addFile('User Report Data.csv', SanitizationStrategy::SNAKE)
                ->toString();

            expect($path)->toBe('documents/user_report_data.csv');
        });
    });

    describe('Error handling and edge cases', function () {
        it('handles files with no extension gracefully', function () {
            $strategies = [
                SanitizationStrategy::SLUG,
                SanitizationStrategy::SNAKE,
                SanitizationStrategy::TIMESTAMP,
                SanitizationStrategy::HASHED
            ];

            foreach ($strategies as $strategy) {
                $path = PathBuilder::base('uploads')
                    ->addFile('README', $strategy)
                    ->toString();

                expect($path)->toStartWith('uploads/');
            }
        });

        it('preserves extensions with special characters', function () {
            $path = PathBuilder::base('uploads')
                ->addFile('document.backup.old', SanitizationStrategy::SLUG)
                ->toString();

            expect($path)->toBe('uploads/documentbackup.old');
        });

        it('handles empty filename with extension', function () {
            $path = PathBuilder::base('uploads')
                ->addFile('.gitignore', SanitizationStrategy::SLUG)
                ->toString();

            expect($path)->toBe('uploads/gitignore');
        });

        it('handles multiple consecutive dots', function () {
            $path = PathBuilder::base('uploads')
                ->addFile('file...txt', SanitizationStrategy::SLUG)
                ->toString();

            expect($path)->toBe('uploads/file.txt');
        });

        it('works with path operations after adding files', function () {
            $path = PathBuilder::base('uploads')
                ->addFile('document.pdf', SanitizationStrategy::SLUG)
                ->replaceExtension('txt');

            expect($path->toString())->toBe('uploads/document.txt')
                ->and($path->getExtension())->toBe('txt')
                ->and($path->getFilename())->toBe('document.txt')
                ->and($path->getFilenameWithoutExtension())->toBe('document');
        });

        it('maintains immutability with extension operations', function () {
            $original = PathBuilder::base('uploads')
                ->addFile('report.xlsx', SanitizationStrategy::TIMESTAMP);

            $modified = $original->replaceExtension('csv');

            expect($original->getExtension())->toBe('xlsx')
                ->and($modified->getExtension())->toBe('csv')
                ->and($original)->not->toBe($modified);
        });
    });

    describe('Integration with validation and safety', function () {
        it('validates paths with preserved extensions', function () {
            $path = PathBuilder::base('uploads')
                ->addFile('safe-file.pdf', SanitizationStrategy::SLUG)
                ->validate();

            expect($path->toString())->toBe('uploads/safe-file.pdf');
        });

        it('validates paths with preserved extensions are safe', function () {
            // SLUG strategy sanitizes unsafe paths, making them safe
            $path = PathBuilder::base('uploads')
                ->addFile('../../../etc/passwd.txt', SanitizationStrategy::SLUG)
                ->validate();

            expect($path->toString())->toBe('uploads/etcpasswd.txt');
        });

        it('rejects truly unsafe paths even with extensions', function () {
            // Create a path that's still unsafe after processing
            expect(fn () => PathBuilder::base('../uploads')
                ->addFile('safe.txt')
                ->validate())
                ->toThrow(UnsafePathException::class);
        });
    });
});