<?php

/**
 * Thrown for Generation Engine pipeline errors.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Exceptions;

use RuntimeException;

/**
 * Per docs/04-Folder-Structure.md's Exceptions/ list. Covers: publishing
 * a resource that failed validation, publishing a resource that hasn't
 * been discovered yet, a filesystem write failure, and rolling back
 * with no prior version to restore.
 */
final class GenerationException extends RuntimeException
{
}
