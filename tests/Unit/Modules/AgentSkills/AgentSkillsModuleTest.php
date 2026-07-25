<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\AgentSkills;

use OxyAI\DTO\ValidationStatus;
use OxyAI\Modules\AgentSkills\AgentSkillsModule;
use OxyAI\Tests\Unit\TestCase;

final class AgentSkillsModuleTest extends TestCase
{
    public function test_exposes_its_identity(): void
    {
        $module = new AgentSkillsModule();

        self::assertSame('agent-skills', $module->id());
        self::assertSame('Agent Skills', $module->name());
        self::assertNotSame('', $module->description());
        self::assertNotSame('', $module->author());
    }

    public function test_has_no_assets_routes_settings_permissions_or_audit_rules_yet(): void
    {
        $module = new AgentSkillsModule();

        self::assertSame([], $module->assets());
        self::assertSame([], $module->routes());
        self::assertSame([], $module->settings());
        self::assertSame([], $module->permissions());
        self::assertSame([], $module->audit());
    }

    public function test_lifecycle_methods_are_safe_no_ops(): void
    {
        $module = new AgentSkillsModule();

        $module->register();
        $module->boot();
        $module->init();
        $module->shutdown();

        $this->expectNotToPerformAssertions();
    }

    public function test_discover_returns_the_registry_resource(): void
    {
        $module = new AgentSkillsModule();

        $resources = $module->discover();

        self::assertCount(1, $resources);
        self::assertSame('agent-skills-registry', $resources[0]->id);
        self::assertSame('/.well-known/agent-skills.json', $resources[0]->location);
        self::assertSame('agent-skills', $resources[0]->module);
    }

    public function test_validate_passes_since_every_skill_has_a_unique_id_and_required_fields(): void
    {
        $module = new AgentSkillsModule();

        $result = $module->validate($module->discover()[0]);

        self::assertSame(ValidationStatus::Pass, $result->status);
        self::assertSame('agent-skills', $result->validator);
    }

    public function test_resource_id_and_supports(): void
    {
        $module = new AgentSkillsModule();

        self::assertSame('agent-skills-registry', $module->resourceId());
        self::assertTrue($module->supports('agent-skills-registry'));
        self::assertFalse($module->supports('robots-txt'));
    }
}
