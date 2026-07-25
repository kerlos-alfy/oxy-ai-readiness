<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\Analytics;

use OxyAI\DTO\ValidationStatus;
use OxyAI\Modules\Analytics\AnalyticsModule;
use OxyAI\Tests\Unit\TestCase;

final class AnalyticsModuleTest extends TestCase
{
    public function test_exposes_its_identity(): void
    {
        $module = new AnalyticsModule();

        self::assertSame('analytics', $module->id());
        self::assertSame('Analytics', $module->name());
        self::assertNotSame('', $module->description());
        self::assertNotSame('', $module->author());
    }

    public function test_has_no_assets_routes_settings_permissions_or_audit_rules_yet(): void
    {
        $module = new AnalyticsModule();

        self::assertSame([], $module->assets());
        self::assertSame([], $module->routes());
        self::assertSame([], $module->settings());
        self::assertSame([], $module->permissions());
        self::assertSame([], $module->audit());
    }

    public function test_lifecycle_methods_are_safe_no_ops(): void
    {
        $module = new AnalyticsModule();

        $module->register();
        $module->boot();
        $module->init();
        $module->shutdown();

        $this->expectNotToPerformAssertions();
    }

    public function test_discover_returns_the_analytics_summary_resource(): void
    {
        $module = new AnalyticsModule();

        $resources = $module->discover();

        self::assertCount(1, $resources);
        self::assertSame('analytics-summary', $resources[0]->id);
        self::assertSame('analytics', $resources[0]->module);
    }

    public function test_validate_passes_since_every_metric_has_a_counter(): void
    {
        $module = new AnalyticsModule();

        $result = $module->validate($module->discover()[0]);

        self::assertSame(ValidationStatus::Pass, $result->status);
        self::assertSame('analytics', $result->validator);
    }

    public function test_resource_id_and_supports(): void
    {
        $module = new AnalyticsModule();

        self::assertSame('analytics-summary', $module->resourceId());
        self::assertTrue($module->supports('analytics-summary'));
        self::assertFalse($module->supports('robots-txt'));
    }
}
