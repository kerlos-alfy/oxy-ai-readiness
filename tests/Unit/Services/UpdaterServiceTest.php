<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Services;

use Brain\Monkey\Functions;
use OxyAI\Services\LicenseService;
use OxyAI\Services\UpdaterService;
use OxyAI\Tests\Unit\TestCase;
use WP_Error;

final class UpdaterServiceTest extends TestCase
{
    private string $fixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtures = dirname(__DIR__, 2) . '/fixtures/updater';
        Functions\when('wp_http_validate_url')->alias(static fn (string $url): string|false => str_starts_with($url, 'https://') ? $url : false);
        Functions\when('__')->returnArg();
    }

    public function test_manifest_parsing_accepts_valid_schema(): void
    {
        $service = new UpdaterService(new LicenseService());
        $result = $service->parseManifest((string) file_get_contents($this->fixtures . '/manifest.json'));

        self::assertIsArray($result);
        self::assertSame('1.0.0', $result['version']);
        self::assertArrayHasKey('signature_url', $result);
    }

    public function test_manifest_parsing_rejects_malformed_json_and_missing_fields(): void
    {
        $service = new UpdaterService(new LicenseService());
        self::assertInstanceOf(WP_Error::class, $service->parseManifest('{not json'));
        self::assertInstanceOf(WP_Error::class, $service->parseManifest('{"version":"1.0.0"}'));
    }

    public function test_version_comparison_newer_same_and_older(): void
    {
        $service = new UpdaterService(new LicenseService());
        self::assertGreaterThan(0, $service->versionRelation('1.0.0'));
        self::assertSame(0, $service->versionRelation('1.0.0-alpha.5'));
        self::assertLessThan(0, $service->versionRelation('1.0.0-alpha.4'));
    }

    public function test_manifest_cache_is_respected_without_remote_request(): void
    {
        $cached = json_decode((string) file_get_contents($this->fixtures . '/manifest.json'), true);
        Functions\when('get_site_transient')->justReturn($cached);
        Functions\expect('wp_remote_get')->never();

        $service = new UpdaterService(new LicenseService());
        self::assertSame($cached, $service->manifest(false));
    }

    public function test_invalid_license_state_does_not_crash_status(): void
    {
        Functions\when('get_site_transient')->justReturn(false);
        Functions\when('get_transient')->justReturn([
            'valid' => false,
            'tier' => null,
            'sites_used' => 0,
            'sites_max' => 0,
            'expires_at' => null,
            'trial' => false,
            'checked_at' => '2026-08-23T00:00:00Z',
            'network_error' => false,
        ]);
        Functions\when('get_option')->justReturn(false);
        Functions\when('current_user_can')->justReturn(false);

        $status = (new UpdaterService(new LicenseService()))->status();
        self::assertFalse($status['license']['valid']);
        self::assertFalse($status['update_available']);
    }

    public function test_signature_verification_with_wrong_key_fails_closed(): void
    {
        $service = new UpdaterService(new LicenseService());
        $devKey = dirname(__DIR__, 3) . '/resources/updater/public-key.pem';
        self::assertFalse($service->verifySignature(
            $this->fixtures . '/sample-signed-archive.zip',
            $this->fixtures . '/sample-signed-archive.zip.sig',
            $devKey
        ));
    }

    public function test_signature_verification_with_tampered_archive_fails_closed(): void
    {
        $copy = tempnam(sys_get_temp_dir(), 'oxy-updater-');
        self::assertIsString($copy);
        copy($this->fixtures . '/sample-signed-archive.zip', $copy);
        file_put_contents($copy, 'tampered', FILE_APPEND);

        try {
            $service = new UpdaterService(new LicenseService());
            self::assertFalse($service->verifySignature(
                $copy,
                $this->fixtures . '/sample-signed-archive.zip.sig',
                $this->fixtures . '/test-public.pem'
            ));
        } finally {
            @unlink($copy);
        }
    }

    public function test_missing_signature_file_fails_closed(): void
    {
        $service = new UpdaterService(new LicenseService());
        self::assertFalse($service->verifySignature(
            $this->fixtures . '/sample-signed-archive.zip',
            $this->fixtures . '/does-not-exist.sig',
            $this->fixtures . '/test-public.pem'
        ));
    }

    public function test_valid_test_fixture_signature_verifies(): void
    {
        $service = new UpdaterService(new LicenseService());
        self::assertTrue($service->verifySignature(
            $this->fixtures . '/sample-signed-archive.zip',
            $this->fixtures . '/sample-signed-archive.zip.sig',
            $this->fixtures . '/test-public.pem'
        ));
    }
}
