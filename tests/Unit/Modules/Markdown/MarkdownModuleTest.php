<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\Markdown;

use OxyAI\DTO\ValidationStatus;
use OxyAI\Modules\Markdown\MarkdownModule;
use OxyAI\Tests\Unit\TestCase;

final class MarkdownModuleTest extends TestCase
{
    public function test_exposes_its_identity(): void
    {
        $module = new MarkdownModule();

        self::assertSame('markdown', $module->id());
        self::assertSame('Markdown', $module->name());
        self::assertNotSame('', $module->description());
        self::assertNotSame('', $module->author());
    }

    public function test_has_no_assets_routes_settings_permissions_or_audit_rules_yet(): void
    {
        $module = new MarkdownModule();

        self::assertSame([], $module->assets());
        self::assertSame([], $module->routes());
        self::assertSame([], $module->settings());
        self::assertSame([], $module->permissions());
        self::assertSame([], $module->audit());
    }

    public function test_lifecycle_methods_are_safe_no_ops(): void
    {
        $module = new MarkdownModule();

        $module->register();
        $module->boot();
        $module->init();
        $module->shutdown();

        $this->expectNotToPerformAssertions();
    }

    public function test_discover_returns_the_markdown_negotiation_resource(): void
    {
        $module = new MarkdownModule();

        $resources = $module->discover();

        self::assertCount(1, $resources);
        self::assertSame('markdown-negotiation', $resources[0]->id);
        self::assertSame('markdown', $resources[0]->module);
    }

    public function test_validate_passes_since_text_markdown_is_declared(): void
    {
        $module = new MarkdownModule();

        $result = $module->validate($module->discover()[0]);

        self::assertSame(ValidationStatus::Pass, $result->status);
        self::assertSame('markdown', $result->validator);
    }

    public function test_resource_id_and_supports(): void
    {
        $module = new MarkdownModule();

        self::assertSame('markdown-negotiation', $module->resourceId());
        self::assertTrue($module->supports('markdown-negotiation'));
        self::assertFalse($module->supports('robots-txt'));
    }
}
