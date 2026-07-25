<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\ApiCatalog;

use OxyAI\DTO\ValidationStatus;
use OxyAI\Modules\ApiCatalog\ApiCatalogModule;
use OxyAI\Tests\Unit\TestCase;

/**
 * No separate `*SnapshotTest` file for this module: unlike every other
 * Phase 8/11/14 module's short, stable generated content, the catalog
 * lists every real route this plugin registers (83 as of Phase 14) —
 * hardcoding that entire JSON string as a literal snapshot would be a
 * huge, fragile fixture that adds no correctness beyond what the
 * structural assertions below already verify (real namespace, every
 * route unique, real known routes present).
 */
final class ApiCatalogModuleTest extends TestCase
{
    public function test_exposes_its_identity(): void
    {
        $module = new ApiCatalogModule();

        self::assertSame('api-catalog', $module->id());
        self::assertSame('API Catalog', $module->name());
        self::assertNotSame('', $module->description());
        self::assertNotSame('', $module->author());
    }

    public function test_has_no_assets_routes_settings_permissions_or_audit_rules_yet(): void
    {
        $module = new ApiCatalogModule();

        self::assertSame([], $module->assets());
        self::assertSame([], $module->routes());
        self::assertSame([], $module->settings());
        self::assertSame([], $module->permissions());
        self::assertSame([], $module->audit());
    }

    public function test_lifecycle_methods_are_safe_no_ops(): void
    {
        $module = new ApiCatalogModule();

        $module->register();
        $module->boot();
        $module->init();
        $module->shutdown();

        $this->expectNotToPerformAssertions();
    }

    public function test_discover_returns_the_catalog_resource(): void
    {
        $module = new ApiCatalogModule();

        $resources = $module->discover();

        self::assertCount(1, $resources);
        self::assertSame('api-catalog', $resources[0]->id);
        self::assertSame('/.well-known/api-catalog', $resources[0]->location);
        self::assertSame('api-catalog', $resources[0]->module);
    }

    public function test_generate_produces_a_non_empty_catalog_of_unique_real_routes(): void
    {
        $decoded = json_decode((new ApiCatalogModule())->generate(), true);

        self::assertSame('oxy-ai/v1', $decoded['namespace']);
        self::assertNotEmpty($decoded['routes']);

        $signatures = array_map(
            static fn (array $route): string => $route['method'] . ' ' . $route['path'],
            $decoded['routes']
        );
        self::assertSame(count($signatures), count(array_unique($signatures)));

        self::assertContains(['method' => 'GET', 'path' => '/score'], $decoded['routes']);
        self::assertContains(['method' => 'GET', 'path' => '/api-catalog'], $decoded['routes']);
    }

    public function test_validate_passes_since_the_catalog_is_non_empty_with_no_duplicates(): void
    {
        $module = new ApiCatalogModule();

        $result = $module->validate($module->discover()[0]);

        self::assertSame(ValidationStatus::Pass, $result->status);
        self::assertSame('api-catalog', $result->validator);
    }

    public function test_resource_id_and_supports(): void
    {
        $module = new ApiCatalogModule();

        self::assertSame('api-catalog', $module->resourceId());
        self::assertTrue($module->supports('api-catalog'));
        self::assertFalse($module->supports('robots-txt'));
    }
}
