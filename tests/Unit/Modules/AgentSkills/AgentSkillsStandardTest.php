<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\AgentSkills;

use OxyAI\Exceptions\ModuleException;
use OxyAI\Modules\AgentSkills\AgentSkillsModule;
use OxyAI\Modules\AgentSkills\AgentSkillsStandard;
use OxyAI\Tests\Unit\TestCase;

final class AgentSkillsStandardTest extends TestCase
{
    public function test_exposes_its_identity(): void
    {
        $standard = new AgentSkillsStandard(new AgentSkillsModule());

        self::assertSame('agent-skills', $standard->id());
        self::assertSame('Agent Skills', $standard->name());
        self::assertNotSame('', $standard->specification());
    }

    public function test_discover_generate_and_validate_delegate_to_the_owning_module(): void
    {
        $module = new AgentSkillsModule();
        $standard = new AgentSkillsStandard($module);

        self::assertSame($module->generate(), $standard->generate());
        self::assertEquals($module->discover(), $standard->discover());
        self::assertSame($module->validate($module->discover()[0])->status, $standard->validate()->status);
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function unimplementedDelegateMethodProvider(): iterable
    {
        yield 'score' => ['score'];
        yield 'monitor' => ['monitor'];
        yield 'report' => ['report'];
    }

    /**
     * @dataProvider unimplementedDelegateMethodProvider
     */
    public function test_delegate_methods_without_an_engine_yet_throw(string $method): void
    {
        $standard = new AgentSkillsStandard(new AgentSkillsModule());

        $this->expectException(ModuleException::class);

        $standard->$method();
    }
}
