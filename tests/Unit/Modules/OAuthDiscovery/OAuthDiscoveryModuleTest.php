<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\OAuthDiscovery;

use Brain\Monkey\Functions;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\DTO\ValidationStatus;
use OxyAI\Modules\OAuthDiscovery\OAuthAuthorizationServerGenerator;
use OxyAI\Modules\OAuthDiscovery\OAuthDiscoveryModule;
use OxyAI\Modules\OAuthDiscovery\OAuthProtectedResourceGenerator;
use OxyAI\Modules\OAuthDiscovery\OpenIdConfigurationGenerator;
use OxyAI\Tests\Unit\TestCase;

final class OAuthDiscoveryModuleTest extends TestCase
{
    private function makeModule(): OAuthDiscoveryModule
    {
        return new OAuthDiscoveryModule(
            new OpenIdConfigurationGenerator(),
            new OAuthAuthorizationServerGenerator(),
            new OAuthProtectedResourceGenerator()
        );
    }

    public function test_exposes_its_identity(): void
    {
        $module = $this->makeModule();

        self::assertSame('oauth-discovery', $module->id());
        self::assertSame('OAuth Discovery', $module->name());
        self::assertNotSame('', $module->description());
        self::assertNotSame('', $module->author());
    }

    public function test_has_no_assets_routes_settings_permissions_or_audit_rules_yet(): void
    {
        $module = $this->makeModule();

        self::assertSame([], $module->assets());
        self::assertSame([], $module->routes());
        self::assertSame([], $module->settings());
        self::assertSame([], $module->permissions());
        self::assertSame([], $module->audit());
    }

    public function test_lifecycle_methods_are_safe_no_ops(): void
    {
        $module = $this->makeModule();

        $module->register();
        $module->boot();
        $module->init();
        $module->shutdown();

        $this->expectNotToPerformAssertions();
    }

    public function test_discover_returns_all_three_well_known_documents(): void
    {
        $module = $this->makeModule();

        $resources = $module->discover();

        self::assertCount(3, $resources);
        self::assertSame(
            ['openid-configuration', 'oauth-authorization-server', 'oauth-protected-resource'],
            array_map(static fn (DiscoveredResource $resource): string => $resource->id, $resources)
        );
        self::assertSame('/.well-known/openid-configuration', $resources[0]->location);
        self::assertSame('/.well-known/oauth-authorization-server', $resources[1]->location);
        self::assertSame('/.well-known/oauth-protected-resource', $resources[2]->location);
    }

    public function test_validate_passes_openid_configuration_when_its_issuer_is_real(): void
    {
        Functions\when('home_url')->justReturn('https://example.test/');

        $module = $this->makeModule();
        $resource = $module->discover()[0];

        $result = $module->validate($resource);

        self::assertSame(ValidationStatus::Pass, $result->status);
        self::assertSame('oauth-discovery', $result->validator);
    }

    public function test_validate_passes_oauth_protected_resource_when_its_resource_field_is_real(): void
    {
        Functions\when('rest_url')->justReturn('https://example.test/wp-json/oxy-ai/v1');

        $module = $this->makeModule();
        $resource = $module->discover()[2];

        $result = $module->validate($resource);

        self::assertSame(ValidationStatus::Pass, $result->status);
    }

    public function test_validate_skips_a_resource_it_does_not_own(): void
    {
        $module = $this->makeModule();

        $foreignResource = new DiscoveredResource(
            id: 'robots-txt',
            type: 'robots-txt',
            location: '/robots.txt',
            status: 'active',
            version: '0.1.0',
            module: 'robots',
            health: 'healthy',
            dependencies: [],
            source: 'robots',
            lastChecked: '2026-07-26T00:00:00+00:00'
        );

        $result = $module->validate($foreignResource);

        self::assertSame(ValidationStatus::Skipped, $result->status);
    }
}
