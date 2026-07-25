<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\Robots;

use OxyAI\Exceptions\ModuleException;
use OxyAI\Modules\Robots\RobotsModule;
use OxyAI\Modules\Robots\RobotsStandard;
use OxyAI\Tests\Unit\TestCase;

final class RobotsStandardTest extends TestCase
{
    public function test_exposes_its_identity(): void
    {
        $standard = new RobotsStandard(new RobotsModule());

        self::assertSame('robots-txt', $standard->id());
        self::assertSame('robots.txt', $standard->name());
        self::assertNotSame('', $standard->specification());
    }

    public function test_discover_generate_and_validate_delegate_to_the_owning_module(): void
    {
        $module = new RobotsModule();
        $standard = new RobotsStandard($module);

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
        $standard = new RobotsStandard(new RobotsModule());

        $this->expectException(ModuleException::class);

        $standard->$method();
    }
}
