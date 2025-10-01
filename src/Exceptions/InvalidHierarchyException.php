<?php

declare(strict_types=1);

namespace Hdaklue\PathBuilder\Exceptions;

use LogicException;

/**
 * Thrown when attempting to add a directory after a file has been added.
 *
 * In a valid file system hierarchy, files cannot contain directories.
 * Once a file is added to the path, no further segments should be appended.
 */
class InvalidHierarchyException extends LogicException
{
    public static function create(): self
    {
        return new self('Cannot add directory segments after a file has been added. Files must be the last segment in a path.');
    }
}
