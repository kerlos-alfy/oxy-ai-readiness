<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Services;

use Brain\Monkey\Functions;
use OxyAI\Services\LicenseService;
use OxyAI\Tests\Unit\TestCase;

final class LicenseServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (!defined('AUTH_KEY')) {
            define('AUTH_KEY', 'unit-test-auth-key');
        }
        if (!defined('SECURE_AUTH_KEY')) {
            define('SECURE_AUTH_KEY', 'unit-test-secure-auth-key');
        }
        Functions\when('__')->returnArg();
    }

    public function test_encryption_decryption_round_trip(): void
    {
        $service = new LicenseService();
        $encrypted = $service->encryptForStorage('oxy_test_license_1234');

        self::assertIsString($encrypted);
        self::assertSame('oxy_test_license_1234', $service->decryptFromStorage($encrypted));
    }

    public function test_license_key_is_not_stored_in_plaintext_payload(): void
    {
        $service = new LicenseService();
        $key = 'oxy_secret_license_value';
        $encrypted = $service->encryptForStorage($key);

        self::assertIsString($encrypted);
        self::assertStringNotContainsString($key, $encrypted);
        self::assertStringStartsWith('v1:', $encrypted);
    }

    public function test_transient_ttl_cache_is_respected(): void
    {
        $cached = [
            'valid' => true,
            'tier' => 'agency',
            'sites_used' => 2,
            'sites_max' => 10,
            'expires_at' => null,
            'trial' => false,
            'checked_at' => '2026-08-23T00:00:00Z',
            'network_error' => false,
        ];
        Functions\when('get_transient')->justReturn($cached);
        Functions\expect('wp_remote_get')->never();

        $state = (new LicenseService())->validate(false);
        self::assertIsArray($state);
        self::assertSame('agency', $state['tier']);
        self::assertSame(2, $state['sites_used']);
    }

    public function test_tier_persistence_uses_24_hour_transient(): void
    {
        $state = [
            'valid' => true,
            'tier' => 'unlimited',
            'sites_used' => 8,
            'sites_max' => 0,
            'expires_at' => null,
            'trial' => false,
            'checked_at' => '2026-08-23T00:00:00Z',
            'network_error' => false,
        ];
        Functions\when('get_transient')->justReturn($state);

        $service = new LicenseService();
        self::assertSame('unlimited', $service->state()['tier']);
        self::assertSame(86400, LicenseService::STATE_TTL);
    }
}
