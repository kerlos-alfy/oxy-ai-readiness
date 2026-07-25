<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\Llms;

use OxyAI\DTO\ValidationStatus;
use OxyAI\Modules\Llms\LlmsModule;
use OxyAI\Tests\Unit\TestCase;

final class LlmsModuleTest extends TestCase
{
    public function test_exposes_its_identity(): void
    {
        $module = new LlmsModule();

        self::assertSame('llms', $module->id());
        self::assertSame('LLMS', $module->name());
        self::assertNotSame('', $module->description());
        self::assertNotSame('', $module->author());
    }

    public function test_has_no_assets_routes_settings_permissions_or_audit_rules_yet(): void
    {
        $module = new LlmsModule();

        self::assertSame([], $module->assets());
        self::assertSame([], $module->routes());
        self::assertSame([], $module->settings());
        self::assertSame([], $module->permissions());
        self::assertSame([], $module->audit());
    }

    public function test_lifecycle_methods_are_safe_no_ops(): void
    {
        $module = new LlmsModule();

        $module->register();
        $module->boot();
        $module->init();
        $module->shutdown();

        $this->expectNotToPerformAssertions();
    }

    public function test_discover_returns_the_llms_txt_resource(): void
    {
        $module = new LlmsModule();

        $resources = $module->discover();

        self::assertCount(1, $resources);
        self::assertSame('llms-txt', $resources[0]->id);
        self::assertSame('/llms.txt', $resources[0]->location);
        self::assertSame('llms', $resources[0]->module);
    }

    public function test_validate_passes_since_title_and_description_are_present(): void
    {
        $module = new LlmsModule();

        $result = $module->validate($module->discover()[0]);

        self::assertSame(ValidationStatus::Pass, $result->status);
        self::assertSame('llms', $result->validator);
    }

    public function test_resource_id_and_supports(): void
    {
        $module = new LlmsModule();

        self::assertSame('llms-txt', $module->resourceId());
        self::assertTrue($module->supports('llms-txt'));
        self::assertFalse($module->supports('robots-txt'));
    }
}
