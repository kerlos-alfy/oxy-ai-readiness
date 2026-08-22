<?php

declare(strict_types=1);

namespace OxyAI\Tests\Integration;

use PHPUnit\Framework\TestCase;
use ZipArchive;

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
        exec(sprintf('bash %s 2>&1', escapeshellarg(self::$repoRoot . '/bin/build-release.sh')), $output, $exitCode);
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
                // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink -- build/test tooling.
                unlink($file);
            }
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- build/test tooling.
            rmdir($buildDir);
        }
    }

    public function test_build_produces_zip_with_matching_md5_and_sha256(): void
    {
        self::assertNotNull(self::$zipPath);
        self::assertFileExists(self::$zipPath . '.md5');
        self::assertFileExists(self::$zipPath . '.sha256');

        $md5 = trim(explode(' ', (string) file_get_contents(self::$zipPath . '.md5'))[0]);
        $sha256 = trim(explode(' ', (string) file_get_contents(self::$zipPath . '.sha256'))[0]);
        self::assertSame($md5, hash_file('md5', self::$zipPath));
        self::assertSame($sha256, hash_file('sha256', self::$zipPath));
    }

    public function test_package_excludes_development_and_test_signing_material(): void
    {
        $names = $this->archiveNames();
        $forbidden = ['tests/', 'fixtures/', '.project/', 'docs/', 'node_modules/', '.git/', '.github/', 'assets/react/', 'test-private.pem'];
        foreach ($forbidden as $needle) {
            foreach ($names as $name) {
                self::assertStringNotContainsString($needle, $name, sprintf('"%s" leaked via "%s".', $needle, $name));
            }
        }
    }

    public function test_package_includes_runtime_paths_and_dev_public_key_only(): void
    {
        $names = $this->archiveNames();
        $required = ['app/', 'routes/', 'dist/', 'resources/updater/public-key.pem', 'oxy-ai-readiness.php', 'uninstall.php', 'vendor/autoload.php'];
        foreach ($required as $needle) {
            self::assertTrue((bool) array_filter($names, static fn (string $name): bool => str_contains($name, $needle)), sprintf('Expected "%s".', $needle));
        }

        self::assertFalse((bool) array_filter($names, static fn (string $name): bool => str_contains($name, 'vendor/phpunit')));

        $zip = new ZipArchive();
        $zip->open((string) self::$zipPath);
        $key = (string) $zip->getFromName('oxy-ai-readiness/resources/updater/public-key.pem');
        $zip->close();
        self::assertStringContainsString('# DEVELOPMENT KEY - REPLACE BEFORE PRODUCTION RELEASE', $key);
        self::assertStringNotContainsString('PRIVATE KEY', $key);
    }

    /** @return array<int, string> */
    private function archiveNames(): array
    {
        $zip = new ZipArchive();
        $zip->open((string) self::$zipPath);
        $names = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (is_string($name)) {
                $names[] = $name;
            }
        }
        $zip->close();
        return $names;
    }
}
