<?php

declare(strict_types=1);

namespace Hdaklue\PathBuilder\Enums;

use Hdaklue\PathBuilder\Strategies\HashedStrategy;
use Hdaklue\PathBuilder\Strategies\SlugStrategy;
use Hdaklue\PathBuilder\Strategies\SnakeStrategy;
use Hdaklue\PathBuilder\Strategies\TimestampStrategy;

/**
 * Enumeration of available sanitization strategies.
 *
 * Provides type-safe access to sanitization strategy classes
 * with better IDE support and preventing typos.
 */
enum SanitizationStrategy: string
{
    case HASHED = HashedStrategy::class;
    case SNAKE = SnakeStrategy::class;
    case SLUG = SlugStrategy::class;
    case TIMESTAMP = TimestampStrategy::class;
}
