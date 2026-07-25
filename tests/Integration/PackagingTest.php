<?php

declare(strict_types=1);

namespace OxyAI\Tests\Integration;

use PHPUnit\Framework\TestCase;
use ZipArchive;

/**
 * Exercises `bin/build-release.sh` for real — a genuine integration
 * test (spawns Composer as a subprocess, writes real files to disk,
 * inspects a real zip archive) per docs/28-Testing-Strategy.md's own
 * "PACKAGE TESTING" checklist ("Correct Files Included," "Development
 * Files Excluded," "Vendor Dependencies Included," "Correct
 * Checksums"). Skips itself (rather than failing) when
 * `dist/.vite/manifest.json` doesn't exist yet — that's `npm run
 * build`'s job, not this script's, and not every environment running
 * `composer test:integration` will have run it first.
 */
final class PackagingTest extends TestCase
{
    private static string $repoRoot;
    private static ?string $zipPath = null;

    public static function setUpBeforeClass(): void
    {
        self::$repoRoot = dirname(__DIR__, 2);

        if (!file_exists(self::$repoRoot . '/dist/.vite/manifest.json')) {
            self::markTestSkipped("dist/.vite/manifest.json is missing — run 'npm run build' first.");
        }

        $output = [];
        $exitCode = 0;
        exec(
            sprintf('bash %s 2>&1', escapeshellarg(self::$repoRoot . '/bin/build-release.sh')),
            $output,
            $exitCode
        );

        if ($exitCode !== 0) {
            self::fail("build-release.sh failed (exit {$exitCode}):\n" . implode("\n", $output));
        }

        foreach (glob(self::$repoRoot . '/build/*.zip') ?: [] as $candidate) {
            self::$zipPath = $candidate;
        }
    }

    public static function tearDownAfterClass(): void
    {
        $buildDir = self::$repoRoot . '/build';

        if (is_dir($buildDir)) {
            foreach (glob($buildDir . '/*') ?: [] as $file) {
                // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink -- build/test tooling, not WordPress runtime; no WP_Filesystem context here.
                unlink($file);
            }

            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- see above.
            rmdir($buildDir);
        }
    }

    public function test_build_produces_a_zip_with_a_matching_checksum_file(): void
    {
        self::assertNotNull(self::$zipPath, 'Expected build-release.sh to produce a .zip file.');
        self::assertFileExists(self::$zipPath . '.sha256');

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- reading this test's own local build artifact, not a remote URL.
        $checksumFileContents = (string) file_get_contents(self::$zipPath . '.sha256');
        $expectedChecksum = trim(explode(' ', $checksumFileContents)[0]);
        self::assertSame($expectedChecksum, hash_file('sha256', self::$zipPath));
    }

    public function test_package_excludes_every_development_only_path(): void
    {
        $zip = new ZipArchive();
        $zip->open((string) self::$zipPath);

        $names = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $names[] = $zip->getNameIndex($i);
        }
        $zip->close();

        $forbiddenPaths = ['tests/', '.project/', 'docs/', 'node_modules/', '.git/', '.github/', 'assets/react/'];

        foreach ($forbiddenPaths as $forbidden) {
            foreach ($names as $name) {
                self::assertStringNotContainsString(
                    $forbidden,
                    $name,
                    sprintf('"%s" leaked into the package via "%s".', $forbidden, $name)
                );
            }
        }
    }

    public function test_package_includes_every_runtime_path_and_no_dev_only_vendor_packages(): void
    {
        $zip = new ZipArchive();
        $zip->open((string) self::$zipPath);

        $names = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $names[] = $zip->getNameIndex($i);
        }
        $zip->close();

        $requiredPaths = ['app/', 'routes/', 'dist/', 'oxy-ai-readiness.php', 'uninstall.php', 'vendor/autoload.php'];

        foreach ($requiredPaths as $required) {
            self::assertTrue(
                (bool) array_filter($names, static fn (string $name): bool => str_contains($name, $required)),
                sprintf('Expected "%s" to be present in the package.', $required)
            );
        }

        self::assertFalse(
            (bool) array_filter($names, static fn (string $name): bool => str_contains($name, 'vendor/phpunit')),
            'Dev-only vendor/phpunit leaked into the package.'
        );
    }
}
