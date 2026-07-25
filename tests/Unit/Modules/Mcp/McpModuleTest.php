<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\Mcp;

use OxyAI\DTO\ValidationStatus;
use OxyAI\Modules\Mcp\McpModule;
use OxyAI\Tests\Unit\TestCase;

final class McpModuleTest extends TestCase
{
    public function test_exposes_its_identity(): void
    {
        $module = new McpModule();

        self::assertSame('mcp', $module->id());
        self::assertSame('MCP', $module->name());
        self::assertNotSame('', $module->description());
        self::assertNotSame('', $module->author());
    }

    public function test_has_no_assets_routes_settings_permissions_or_audit_rules_yet(): void
    {
        $module = new McpModule();

        self::assertSame([], $module->assets());
        self::assertSame([], $module->routes());
        self::assertSame([], $module->settings());
        self::assertSame([], $module->permissions());
        self::assertSame([], $module->audit());
    }

    public function test_lifecycle_methods_are_safe_no_ops(): void
    {
        $module = new McpModule();

        $module->register();
        $module->boot();
        $module->init();
        $module->shutdown();

        $this->expectNotToPerformAssertions();
    }

    public function test_discover_returns_the_server_card_resource(): void
    {
        $module = new McpModule();

        $resources = $module->discover();

        self::assertCount(1, $resources);
        self::assertSame('mcp-server-card', $resources[0]->id);
        self::assertSame('/.well-known/mcp.json', $resources[0]->location);
        self::assertSame('mcp', $resources[0]->module);
    }

    public function test_validate_passes_since_every_required_field_is_present(): void
    {
        $module = new McpModule();

        $result = $module->validate($module->discover()[0]);

        self::assertSame(ValidationStatus::Pass, $result->status);
        self::assertSame('mcp', $result->validator);
    }

    public function test_resource_id_and_supports(): void
    {
        $module = new McpModule();

        self::assertSame('mcp-server-card', $module->resourceId());
        self::assertTrue($module->supports('mcp-server-card'));
        self::assertFalse($module->supports('robots-txt'));
    }
}
