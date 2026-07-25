<?php

/**
 * Repository wrapping safe, path-confined filesystem access via WP_Filesystem.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Repositories;

use InvalidArgumentException;
use OxyAI\Contracts\RepositoryInterface;
use RuntimeException;
use WP_Filesystem_Base;

/**
 * Wraps the WordPress Filesystem API (`WP_Filesystem_Base`, resolved via
 * `WP_Filesystem()`), confined to a single base directory, with atomic
 * writes and checksum support.
 *
 * Implements the Filesystem Security requirements from
 * docs/26-Security-Spec.md: canonical Path Validation (every relative path
 * is resolved and verified to stay within the allowed base directory —
 * `..` traversal and absolute paths are rejected outright), Safe/Atomic
 * Writes (content is written to a temporary sibling file and then moved
 * into place), and Checksum Verification.
 *
 * Construction is intentionally decoupled from `wp_upload_dir()` /
 * `WP_Filesystem()` — callers (a future ServiceProvider) pass an already
 * -resolved absolute base directory and, optionally, an already
 * -initialized filesystem instance. This keeps the class trivially
 * testable (inject a mock `WP_Filesystem_Base`) without needing to bootstrap
 * WordPress or touch the real filesystem.
 */
final class FileRepository implements RepositoryInterface
{
    private readonly string $baseDir;
    private ?WP_Filesystem_Base $filesystem;

    public function __construct(string $baseDir, ?WP_Filesystem_Base $filesystem = null)
    {
        $this->baseDir = rtrim($baseDir, "/\\");
        $this->filesystem = $filesystem;
    }

    public function read(string $relativePath): ?string
    {
        $path = $this->resolvePath($relativePath);
        $fs = $this->filesystem();

        if (!$fs->exists($path) || !$fs->is_readable($path)) {
            return null;
        }

        $contents = $fs->get_contents($path);

        return $contents === false ? null : $contents;
    }

    public function write(string $relativePath, string $contents): bool
    {
        $path = $this->resolvePath($relativePath);
        $fs = $this->filesystem();

        if (!$this->ensureDirectoryExists($fs, dirname($path))) {
            return false;
        }

        $tempPath = $path . '.tmp-' . bin2hex(random_bytes(8));

        if (!$fs->put_contents($tempPath, $contents, 0644)) {
            $fs->delete($tempPath);

            return false;
        }

        if (!$fs->move($tempPath, $path, true)) {
            $fs->delete($tempPath);

            return false;
        }

        return true;
    }

    public function delete(string $relativePath): bool
    {
        $path = $this->resolvePath($relativePath);
        $fs = $this->filesystem();

        if (!$fs->exists($path)) {
            return true;
        }

        return $fs->delete($path);
    }

    public function exists(string $relativePath): bool
    {
        return $this->filesystem()->exists($this->resolvePath($relativePath));
    }

    public function checksum(string $relativePath): ?string
    {
        $contents = $this->read($relativePath);

        return $contents === null ? null : hash('sha256', $contents);
    }

    /**
     * Resolves a caller-supplied relative path to an absolute path,
     * guaranteeing the result stays within {@see self::$baseDir}.
     *
     * Rejects: empty paths, null bytes, absolute paths, and any path
     * containing a literal ".." segment — the last of these is a blanket
     * rule rather than a resolve-then-check strategy, since the target
     * file may not exist yet (write case) and therefore realpath()
     * resolution cannot be relied upon.
     */
    private function resolvePath(string $relativePath): string
    {
        if ($relativePath === '' || str_contains($relativePath, "\0")) {
            throw new InvalidArgumentException('Invalid file path.');
        }

        $normalized = str_replace('\\', '/', $relativePath);

        if (str_starts_with($normalized, '/') || preg_match('#^[A-Za-z]:#', $normalized) === 1) {
            throw new InvalidArgumentException('Absolute paths are not permitted.');
        }

        $segments = explode('/', $normalized);

        foreach ($segments as $segment) {
            if ($segment === '..' || $segment === '') {
                throw new InvalidArgumentException(
                    sprintf('Invalid file path "%s": path traversal is not permitted.', $relativePath)
                );
            }
        }

        return $this->baseDir . '/' . implode('/', $segments);
    }

    /**
     * Recursively ensures {@see $directory} exists, creating missing parent
     * segments one level at a time.
     *
     * `WP_Filesystem_Base::mkdir()` has no native recursive option (unlike
     * PHP's own `mkdir()`) — its third and fourth parameters are `$chown`
     * and `$chgrp`, not a recursion flag. Recursion stops at {@see
     * self::$baseDir}, which is assumed to already exist.
     */
    private function ensureDirectoryExists(WP_Filesystem_Base $fs, string $directory): bool
    {
        if ($directory === $this->baseDir || $fs->is_dir($directory)) {
            return true;
        }

        $parent = dirname($directory);

        if ($parent === $directory || !$this->ensureDirectoryExists($fs, $parent)) {
            return false;
        }

        return $fs->mkdir($directory, 0755);
    }

    private function filesystem(): WP_Filesystem_Base
    {
        if ($this->filesystem instanceof WP_Filesystem_Base) {
            return $this->filesystem;
        }

        global $wp_filesystem;

        if (!$wp_filesystem instanceof WP_Filesystem_Base) {
            WP_Filesystem();
        }

        if (!$wp_filesystem instanceof WP_Filesystem_Base) {
            throw new RuntimeException('Unable to initialize the WordPress filesystem.');
        }

        $this->filesystem = $wp_filesystem;

        return $this->filesystem;
    }
}
