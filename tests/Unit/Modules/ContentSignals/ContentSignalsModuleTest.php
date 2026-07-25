<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\ContentSignals;

use OxyAI\DTO\ValidationStatus;
use OxyAI\Modules\ContentSignals\ContentSignalsModule;
use OxyAI\Tests\Unit\TestCase;

final class ContentSignalsModuleTest extends TestCase
{
    public function test_exposes_its_identity(): void
    {
        $module = new ContentSignalsModule();

        self::assertSame('content-signals', $module->id());
        self::assertSame('Content Signals', $module->name());
        self::assertNotSame('', $module->description());
        self::assertNotSame('', $module->author());
    }

    public function test_has_no_assets_routes_settings_permissions_or_audit_rules_yet(): void
    {
        $module = new ContentSignalsModule();

        self::assertSame([], $module->assets());
        self::assertSame([], $module->routes());
        self::assertSame([], $module->settings());
        self::assertSame([], $module->permissions());
        self::assertSame([], $module->audit());
    }

    public function test_lifecycle_methods_are_safe_no_ops(): void
    {
        $module = new ContentSignalsModule();

        $module->register();
        $module->boot();
        $module->init();
        $module->shutdown();

        $this->expectNotToPerformAssertions();
    }

    public function test_discover_returns_the_content_signals_resource(): void
    {
        $module = new ContentSignalsModule();

        $resources = $module->discover();

        self::assertCount(1, $resources);
        self::assertSame('content-signals', $resources[0]->id);
        self::assertSame('content-signals', $resources[0]->module);
    }

    public function test_validate_passes_since_the_default_signals_have_no_duplicates(): void
    {
        $module = new ContentSignalsModule();

        $result = $module->validate($module->discover()[0]);

        self::assertSame(ValidationStatus::Pass, $result->status);
        self::assertSame('content-signals', $result->validator);
    }

    public function test_resource_id_and_supports(): void
    {
        $module = new ContentSignalsModule();

        self::assertSame('content-signals', $module->resourceId());
        self::assertTrue($module->supports('content-signals'));
        self::assertFalse($module->supports('robots-txt'));
    }
}
