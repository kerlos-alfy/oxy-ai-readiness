<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\Mcp;

use OxyAI\Exceptions\ModuleException;
use OxyAI\Modules\Mcp\McpModule;
use OxyAI\Modules\Mcp\McpStandard;
use OxyAI\Tests\Unit\TestCase;

final class McpStandardTest extends TestCase
{
    public function test_exposes_its_identity(): void
    {
        $standard = new McpStandard(new McpModule());

        self::assertSame('mcp', $standard->id());
        self::assertSame('Model Context Protocol', $standard->name());
        self::assertNotSame('', $standard->specification());
    }

    public function test_discover_generate_and_validate_delegate_to_the_owning_module(): void
    {
        $module = new McpModule();
        $standard = new McpStandard($module);

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
        $standard = new McpStandard(new McpModule());

        $this->expectException(ModuleException::class);

        $standard->$method();
    }
}
