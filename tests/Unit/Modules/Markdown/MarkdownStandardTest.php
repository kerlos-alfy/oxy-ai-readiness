<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\Markdown;

use OxyAI\Exceptions\ModuleException;
use OxyAI\Modules\Markdown\MarkdownModule;
use OxyAI\Modules\Markdown\MarkdownStandard;
use OxyAI\Tests\Unit\TestCase;

final class MarkdownStandardTest extends TestCase
{
    public function test_exposes_its_identity(): void
    {
        $standard = new MarkdownStandard(new MarkdownModule());

        self::assertSame('markdown-negotiation', $standard->id());
        self::assertSame('Markdown Negotiation', $standard->name());
        self::assertNotSame('', $standard->specification());
    }

    public function test_discover_generate_and_validate_delegate_to_the_owning_module(): void
    {
        $module = new MarkdownModule();
        $standard = new MarkdownStandard($module);

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
        $standard = new MarkdownStandard(new MarkdownModule());

        $this->expectException(ModuleException::class);

        $standard->$method();
    }
}
