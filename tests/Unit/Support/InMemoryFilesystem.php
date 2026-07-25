<?php

/**
 * In-memory WP_Filesystem_Base test double.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Support;

use WP_Filesystem_Base;

/**
 * A real (if trivial) filesystem backed by a PHP array, for tests that
 * exercise `FileRepository`'s multi-step read/write/move sequences
 * (e.g. `GenerationService`'s publish/rollback pipeline) where
 * expecting every individual `WP_Filesystem_Base` call via Mockery
 * would be brittle. Not a WordPress core stand-in — lives under
 * `tests/Unit/Support`, not `tests/stubs`, since it's test-double
 * behavior, not a mirror of a real WP core class's own logic.
 */
final class InMemoryFilesystem extends WP_Filesystem_Base
{
    /** @var array<string, string> */
    private array $files = [];

    public function exists(string $file): bool
    {
        return isset($this->files[$file]);
    }

    public function is_readable(string $file): bool
    {
        return isset($this->files[$file]);
    }

    public function is_writable(string $file): bool
    {
        return true;
    }

    public function is_dir(string $path): bool
    {
        return false;
    }

    public function get_contents(string $file)
    {
        return $this->files[$file] ?? false;
    }

    public function put_contents(string $file, string $contents, $mode = false): bool
    {
        $this->files[$file] = $contents;

        return true;
    }

    public function delete(string $file, bool $recursive = false, ?string $type = null): bool
    {
        unset($this->files[$file]);

        return true;
    }

    public function mkdir(string $path, $chmod = false, $chown = false, $chgrp = false): bool
    {
        return true;
    }

    public function move(string $source, string $destination, bool $overwrite = false): bool
    {
        if (!isset($this->files[$source])) {
            return false;
        }

        $this->files[$destination] = $this->files[$source];
        unset($this->files[$source]);

        return true;
    }
}
