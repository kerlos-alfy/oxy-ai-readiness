<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\Headers;

use OxyAI\DTO\ValidationStatus;
use OxyAI\Modules\Headers\HeadersModule;
use OxyAI\Tests\Unit\TestCase;

final class HeadersModuleTest extends TestCase
{
    public function test_exposes_its_identity(): void
    {
        $module = new HeadersModule();

        self::assertSame('headers', $module->id());
        self::assertSame('Headers', $module->name());
        self::assertNotSame('', $module->description());
        self::assertNotSame('', $module->author());
    }

    public function test_has_no_assets_routes_settings_permissions_or_audit_rules_yet(): void
    {
        $module = new HeadersModule();

        self::assertSame([], $module->assets());
        self::assertSame([], $module->routes());
        self::assertSame([], $module->settings());
        self::assertSame([], $module->permissions());
        self::assertSame([], $module->audit());
    }

    public function test_lifecycle_methods_are_safe_no_ops(): void
    {
        $module = new HeadersModule();

        $module->register();
        $module->boot();
        $module->init();
        $module->shutdown();

        $this->expectNotToPerformAssertions();
    }

    public function test_discover_returns_the_http_headers_resource(): void
    {
        $module = new HeadersModule();

        $resources = $module->discover();

        self::assertCount(1, $resources);
        self::assertSame('http-headers', $resources[0]->id);
        self::assertSame('headers', $resources[0]->module);
    }

    public function test_validate_passes_since_the_default_headers_have_no_duplicates(): void
    {
        $module = new HeadersModule();

        $result = $module->validate($module->discover()[0]);

        self::assertSame(ValidationStatus::Pass, $result->status);
        self::assertSame('headers', $result->validator);
    }

    public function test_resource_id_and_supports(): void
    {
        $module = new HeadersModule();

        self::assertSame('http-headers', $module->resourceId());
        self::assertTrue($module->supports('http-headers'));
        self::assertFalse($module->supports('robots-txt'));
    }
}
