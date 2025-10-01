<?php

use Hdaklue\PathBuilder\Exceptions\InvalidHierarchyException;
use Hdaklue\PathBuilder\PathBuilder;

describe('Path Hierarchy Validation', function () {
    describe('InvalidHierarchyException', function () {
        it('throws exception when add() is called after addFile()', function () {
            expect(fn () => PathBuilder::base('uploads')
                ->addFile('document.pdf')
                ->add('subdir'))
                ->toThrow(InvalidHierarchyException::class);
        });

        it('throws exception when addTimestampedDir() is called after addFile()', function () {
            expect(fn () => PathBuilder::base('uploads')
                ->addFile('document.pdf')
                ->addTimestampedDir())
                ->toThrow(InvalidHierarchyException::class);
        });

        it('throws exception when addHashedDir() is called after addFile()', function () {
            expect(fn () => PathBuilder::base('uploads')
                ->addFile('document.pdf')
                ->addHashedDir('user-123'))
                ->toThrow(InvalidHierarchyException::class);
        });

        it('allows proper hierarchy with directories before file', function () {
            $path = PathBuilder::base('uploads')
                ->add('users')
                ->addTimestampedDir()
                ->addHashedDir('user-123')
                ->addFile('document.pdf')
                ->toString();

            expect($path)->toMatch('/^uploads\/users\/\d+\/[a-f0-9]{32}\/document\.pdf$/');
        });

        it('allows adding multiple directories without a file', function () {
            $path = PathBuilder::base('uploads')
                ->add('users')
                ->addTimestampedDir()
                ->addHashedDir('user-123')
                ->toString();

            expect($path)->toMatch('/^uploads\/users\/\d+\/[a-f0-9]{32}$/');
        });

        it('provides clear error message', function () {
            try {
                PathBuilder::base('uploads')
                    ->addFile('document.pdf')
                    ->add('subdir');
            } catch (InvalidHierarchyException $e) {
                expect($e->getMessage())
                    ->toBe('Cannot add directory segments after a file has been added. Files must be the last segment in a path.');
            }
        });
    });

    describe('Valid hierarchy patterns', function () {
        it('allows single file without directories', function () {
            $path = PathBuilder::base('uploads')
                ->addFile('document.pdf')
                ->toString();

            expect($path)->toBe('uploads/document.pdf');
        });

        it('allows directories then file', function () {
            $path = PathBuilder::base('uploads')
                ->add('users')
                ->add('documents')
                ->addFile('report.pdf')
                ->toString();

            expect($path)->toBe('uploads/users/documents/report.pdf');
        });

        it('allows only directories without file', function () {
            $path = PathBuilder::base('uploads')
                ->add('users')
                ->add('documents')
                ->toString();

            expect($path)->toBe('uploads/users/documents');
        });
    });
});
