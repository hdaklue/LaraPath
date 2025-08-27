<?php

declare(strict_types=1);

namespace Hdaklue\PathBuilder\Tests\Unit;

use Hdaklue\PathBuilder\Enums\SanitizationStrategy;
use Hdaklue\PathBuilder\Exceptions\UnsafePathException;
use Hdaklue\PathBuilder\PathBuilder;
use PHPUnit\Framework\TestCase;

class PathBuilderTest extends TestCase
{
    public function test_base_creates_path_builder_instance(): void
    {
        $builder = PathBuilder::base('uploads');

        $this->assertEquals('uploads', $builder->toString());
    }

    public function test_add_method_appends_path_segments(): void
    {
        $path = PathBuilder::base('uploads')
            ->add('images')
            ->add('avatar.jpg')
            ->toString();

        $this->assertEquals('uploads/images/avatar.jpg', $path);
    }

    public function test_automatic_slash_trimming(): void
    {
        $path = PathBuilder::base('/uploads/')
            ->add('/images/')
            ->add('/avatar.jpg/')
            ->toString();

        $this->assertEquals('uploads/images/avatar.jpg', $path);
    }

    public function test_hashed_sanitization_strategy(): void
    {
        $path = PathBuilder::base('uploads')
            ->add('user@email.com', SanitizationStrategy::HASHED)
            ->toString();

        $this->assertEquals('uploads/'.md5('user@email.com'), $path);
    }

    public function test_slug_sanitization_strategy(): void
    {
        $path = PathBuilder::base('uploads')
            ->add('My Amazing File!', SanitizationStrategy::SLUG)
            ->toString();

        $this->assertEquals('uploads/my-amazing-file', $path);
    }

    public function test_snake_sanitization_strategy(): void
    {
        $path = PathBuilder::base('uploads')
            ->add('CamelCase Name', SanitizationStrategy::SNAKE)
            ->toString();

        $this->assertEquals('uploads/camel_case_name', $path);
    }

    public function test_timestamp_sanitization_strategy(): void
    {
        $timestamp = time();

        $path = PathBuilder::base('temp')
            ->add('session', SanitizationStrategy::TIMESTAMP)
            ->toString();

        $this->assertStringStartsWith('temp/session_', $path);
        $this->assertStringContainsString((string) $timestamp, $path);
    }

    public function test_immutable_operations(): void
    {
        $base = PathBuilder::base('uploads');
        $images = $base->add('images');
        $videos = $base->add('videos');

        $this->assertEquals('uploads', $base->toString());
        $this->assertEquals('uploads/images', $images->toString());
        $this->assertEquals('uploads/videos', $videos->toString());
    }

    public function test_get_extension(): void
    {
        $builder = PathBuilder::base('files/video.mp4');

        $this->assertEquals('mp4', $builder->getExtension());
    }

    public function test_get_filename(): void
    {
        $builder = PathBuilder::base('files/video.mp4');

        $this->assertEquals('video.mp4', $builder->getFilename());
    }

    public function test_get_filename_without_extension(): void
    {
        $builder = PathBuilder::base('files/video.mp4');

        $this->assertEquals('video', $builder->getFilenameWithoutExtension());
    }

    public function test_get_directory_path(): void
    {
        $builder = PathBuilder::base('files/video.mp4');

        $this->assertEquals('files', $builder->getDirectoryPath());
    }

    public function test_replace_extension(): void
    {
        $newPath = PathBuilder::base('files/video.mp4')
            ->replaceExtension('webm')
            ->toString();

        $this->assertEquals('files/video.webm', $newPath);
    }

    public function test_add_timestamped_dir(): void
    {
        $timestamp = time();
        $path = PathBuilder::base('uploads')
            ->addTimestampedDir()
            ->toString();

        $this->assertStringStartsWith('uploads/', $path);
        $this->assertStringContainsString((string) $timestamp, $path);
    }

    public function test_add_hashed_dir(): void
    {
        $path = PathBuilder::base('uploads')
            ->addHashedDir('user123')
            ->toString();

        $this->assertEquals('uploads/'.md5('user123'), $path);
    }

    public function test_normalize_removes_duplicate_slashes(): void
    {
        $normalized = PathBuilder::normalize('uploads//images///avatar.jpg');

        $this->assertEquals('uploads/images/avatar.jpg', $normalized);
    }

    public function test_is_safe_detects_directory_traversal(): void
    {
        $this->assertFalse(PathBuilder::isSafe('../etc/passwd'));
        $this->assertFalse(PathBuilder::isSafe('uploads/../../../etc/passwd'));
        $this->assertTrue(PathBuilder::isSafe('uploads/images/avatar.jpg'));
    }

    public function test_validate_throws_exception_for_unsafe_paths(): void
    {
        $this->expectException(UnsafePathException::class);
        $this->expectExceptionMessage('Unsafe path detected');

        PathBuilder::base('uploads')
            ->add('../../../etc/passwd')
            ->validate();
    }

    public function test_build_static_method(): void
    {
        $path = PathBuilder::build(['uploads', 'images', 'avatar.jpg']);

        $this->assertEquals('uploads/images/avatar.jpg', $path);
    }

    public function test_join_static_method(): void
    {
        $path = PathBuilder::join('uploads', 'images', 'avatar.jpg');

        $this->assertEquals('uploads/images/avatar.jpg', $path);
    }

    public function test_build_relative_path(): void
    {
        $relative = PathBuilder::buildRelativePath('/var/www/uploads/image.jpg', '/var/www');

        $this->assertEquals('uploads/image.jpg', $relative);
    }

    public function test_debug_returns_path_information(): void
    {
        $debug = PathBuilder::base('files/video.mp4')->debug();

        $this->assertIsArray($debug);
        $this->assertArrayHasKey('segments', $debug);
        $this->assertArrayHasKey('final_path', $debug);
        $this->assertArrayHasKey('is_safe', $debug);
        $this->assertArrayHasKey('extension', $debug);
        $this->assertArrayHasKey('filename', $debug);
        $this->assertEquals('files/video.mp4', $debug['final_path']);
        $this->assertEquals('mp4', $debug['extension']);
        $this->assertEquals('video.mp4', $debug['filename']);
        $this->assertTrue($debug['is_safe']);
    }

    public function test_to_string_magic_method(): void
    {
        $builder = PathBuilder::base('uploads')->add('images');

        $this->assertEquals('uploads/images', (string) $builder);
    }
}
