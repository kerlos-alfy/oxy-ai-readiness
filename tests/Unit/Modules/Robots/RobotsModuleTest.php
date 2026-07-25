<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\Robots;

use OxyAI\DTO\ValidationStatus;
use OxyAI\Modules\Robots\RobotsModule;
use OxyAI\Tests\Unit\TestCase;

final class RobotsModuleTest extends TestCase
{
    public function test_exposes_its_identity(): void
    {
        $module = new RobotsModule();

        self::assertSame('robots', $module->id());
        self::assertSame('Robots', $module->name());
        self::assertNotSame('', $module->description());
        self::assertNotSame('', $module->author());
    }

    public function test_has_no_assets_routes_settings_permissions_or_audit_rules_yet(): void
    {
        $module = new RobotsModule();

        self::assertSame([], $module->assets());
        self::assertSame([], $module->routes());
        self::assertSame([], $module->settings());
        self::assertSame([], $module->permissions());
        self::assertSame([], $module->audit());
    }

    public function test_lifecycle_methods_are_safe_no_ops(): void
    {
        $module = new RobotsModule();

        $module->register();
        $module->boot();
        $module->init();
        $module->shutdown();

        $this->expectNotToPerformAssertions();
    }

    public function test_discover_returns_the_robots_txt_resource(): void
    {
        $module = new RobotsModule();

        $resources = $module->discover();

        self::assertCount(1, $resources);
        self::assertSame('robots-txt', $resources[0]->id);
        self::assertSame('/robots.txt', $resources[0]->location);
        self::assertSame('robots', $resources[0]->module);
    }

    public function test_validate_passes_since_the_default_ruleset_has_no_duplicate_user_agents(): void
    {
        $module = new RobotsModule();

        $result = $module->validate($module->discover()[0]);

        self::assertSame(ValidationStatus::Pass, $result->status);
        self::assertSame('robots', $result->validator);
    }

    public function test_resource_id_and_supports(): void
    {
        $module = new RobotsModule();

        self::assertSame('robots-txt', $module->resourceId());
        self::assertTrue($module->supports('robots-txt'));
        self::assertFalse($module->supports('llms-txt'));
    }
}
