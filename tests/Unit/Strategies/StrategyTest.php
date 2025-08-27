<?php

declare(strict_types=1);

namespace Hdaklue\PathBuilder\Tests\Unit\Strategies;

use Hdaklue\PathBuilder\Strategies\HashedStrategy;
use Hdaklue\PathBuilder\Strategies\SlugStrategy;
use Hdaklue\PathBuilder\Strategies\SnakeStrategy;
use Hdaklue\PathBuilder\Strategies\TimestampStrategy;
use PHPUnit\Framework\TestCase;

class StrategyTest extends TestCase
{
    public function test_hashed_strategy_creates_md5_hash(): void
    {
        $result = HashedStrategy::apply('test@example.com');

        $this->assertEquals(md5('test@example.com'), $result);
        $this->assertEquals(32, strlen($result)); // MD5 is 32 characters
    }

    public function test_hashed_strategy_with_different_algorithm(): void
    {
        $result = HashedStrategy::apply('test', 'sha256');

        $this->assertEquals(hash('sha256', 'test'), $result);
        $this->assertEquals(64, strlen($result)); // SHA256 is 64 characters
    }

    public function test_slug_strategy_creates_url_friendly_slug(): void
    {
        $result = SlugStrategy::apply('My Amazing File!');

        $this->assertEquals('my-amazing-file', $result);
    }

    public function test_slug_strategy_handles_special_characters(): void
    {
        $result = SlugStrategy::apply('File with @#$%^&*() characters');

        $this->assertEquals('file-with-characters', $result);
    }

    public function test_snake_strategy_converts_to_snake_case(): void
    {
        $result = SnakeStrategy::apply('CamelCaseName');

        $this->assertEquals('camel_case_name', $result);
    }

    public function test_snake_strategy_handles_spaces(): void
    {
        $result = SnakeStrategy::apply('Multiple Word String');

        $this->assertEquals('multiple_word_string', $result);
    }

    public function test_timestamp_strategy_appends_timestamp(): void
    {
        $input = 'session';
        $timestamp = time();

        $result = TimestampStrategy::apply($input);

        $this->assertStringStartsWith($input.'_', $result);
        $this->assertStringContainsString((string) $timestamp, $result);
    }

    public function test_timestamp_strategy_preserves_original_input(): void
    {
        $input = 'my-file';
        $result = TimestampStrategy::apply($input);

        $this->assertStringStartsWith($input.'_', $result);
    }
}
