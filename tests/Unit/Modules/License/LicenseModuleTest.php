<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\License;

use OxyAI\DTO\ValidationStatus;
use OxyAI\Modules\License\LicenseModule;
use OxyAI\Tests\Unit\TestCase;

final class LicenseModuleTest extends TestCase
{
    public function test_exposes_its_identity(): void
    {
        $module = new LicenseModule();

        self::assertSame('license', $module->id());
        self::assertSame('License', $module->name());
        self::assertNotSame('', $module->description());
        self::assertNotSame('', $module->author());
    }

    public function test_has_no_assets_routes_settings_permissions_or_audit_rules_yet(): void
    {
        $module = new LicenseModule();

        self::assertSame([], $module->assets());
        self::assertSame([], $module->routes());
        self::assertSame([], $module->settings());
        self::assertSame([], $module->permissions());
        self::assertSame([], $module->audit());
    }

    public function test_lifecycle_methods_are_safe_no_ops(): void
    {
        $module = new LicenseModule();

        $module->register();
        $module->boot();
        $module->init();
        $module->shutdown();

        $this->expectNotToPerformAssertions();
    }

    public function test_discover_returns_the_license_status_resource(): void
    {
        $module = new LicenseModule();

        $resources = $module->discover();

        self::assertCount(1, $resources);
        self::assertSame('license-status', $resources[0]->id);
        self::assertSame('license', $resources[0]->module);
    }

    public function test_validate_passes_since_the_declaration_is_well_formed(): void
    {
        $module = new LicenseModule();

        $result = $module->validate($module->discover()[0]);

        self::assertSame(ValidationStatus::Pass, $result->status);
        self::assertSame('license', $result->validator);
    }

    public function test_resource_id_and_supports(): void
    {
        $module = new LicenseModule();

        self::assertSame('license-status', $module->resourceId());
        self::assertTrue($module->supports('license-status'));
        self::assertFalse($module->supports('robots-txt'));
    }
}
