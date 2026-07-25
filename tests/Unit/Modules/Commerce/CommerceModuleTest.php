<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\Commerce;

use OxyAI\DTO\ValidationStatus;
use OxyAI\Modules\Commerce\CommerceModule;
use OxyAI\Tests\Unit\TestCase;

final class CommerceModuleTest extends TestCase
{
    public function test_exposes_its_identity(): void
    {
        $module = new CommerceModule();

        self::assertSame('commerce', $module->id());
        self::assertSame('Commerce', $module->name());
        self::assertNotSame('', $module->description());
        self::assertNotSame('', $module->author());
    }

    public function test_has_no_assets_routes_settings_permissions_or_audit_rules_yet(): void
    {
        $module = new CommerceModule();

        self::assertSame([], $module->assets());
        self::assertSame([], $module->routes());
        self::assertSame([], $module->settings());
        self::assertSame([], $module->permissions());
        self::assertSame([], $module->audit());
    }

    public function test_lifecycle_methods_are_safe_no_ops(): void
    {
        $module = new CommerceModule();

        $module->register();
        $module->boot();
        $module->init();
        $module->shutdown();

        $this->expectNotToPerformAssertions();
    }

    public function test_discover_returns_the_commerce_status_resource(): void
    {
        $module = new CommerceModule();

        $resources = $module->discover();

        self::assertCount(1, $resources);
        self::assertSame('commerce-status', $resources[0]->id);
        self::assertSame('commerce', $resources[0]->module);
    }

    public function test_validate_passes_since_the_declaration_is_well_formed(): void
    {
        $module = new CommerceModule();

        $result = $module->validate($module->discover()[0]);

        self::assertSame(ValidationStatus::Pass, $result->status);
        self::assertSame('commerce', $result->validator);
    }

    public function test_resource_id_and_supports(): void
    {
        $module = new CommerceModule();

        self::assertSame('commerce-status', $module->resourceId());
        self::assertTrue($module->supports('commerce-status'));
        self::assertFalse($module->supports('robots-txt'));
    }
}
