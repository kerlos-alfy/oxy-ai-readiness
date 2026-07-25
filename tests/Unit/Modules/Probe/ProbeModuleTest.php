<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\Probe;

use OxyAI\Modules\Probe\ProbeModule;
use OxyAI\Tests\Unit\TestCase;

final class ProbeModuleTest extends TestCase
{
    public function test_exposes_its_identity(): void
    {
        $module = new ProbeModule();

        self::assertSame('probe', $module->id());
        self::assertSame('Probe', $module->name());
        self::assertSame('0.1.0', $module->version());
        self::assertNotSame('', $module->description());
        self::assertNotSame('', $module->author());
    }

    public function test_has_no_assets_routes_settings_permissions_or_audit_rules_yet(): void
    {
        $module = new ProbeModule();

        self::assertSame([], $module->assets());
        self::assertSame([], $module->routes());
        self::assertSame([], $module->settings());
        self::assertSame([], $module->permissions());
        self::assertSame([], $module->audit());
    }

    public function test_lifecycle_methods_are_safe_no_ops(): void
    {
        $module = new ProbeModule();

        $module->register();
        $module->boot();
        $module->init();
        $module->shutdown();

        $this->expectNotToPerformAssertions();
    }

    public function test_discover_returns_exactly_one_fixture_resource(): void
    {
        $module = new ProbeModule();

        $resources = $module->discover();

        self::assertCount(1, $resources);
        self::assertSame('probe-fixture', $resources[0]->id);
        self::assertSame('probe', $resources[0]->module);
        self::assertSame('healthy', $resources[0]->health);
    }
}
