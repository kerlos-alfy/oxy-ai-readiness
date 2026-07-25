<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\Probe;

use OxyAI\DTO\ValidationStatus;
use OxyAI\Exceptions\ModuleException;
use OxyAI\Modules\Probe\ProbeModule;
use OxyAI\Modules\Probe\ProbeStandard;
use OxyAI\Tests\Unit\TestCase;

final class ProbeStandardTest extends TestCase
{
    public function test_exposes_its_identity(): void
    {
        $standard = new ProbeStandard(new ProbeModule());

        self::assertSame('probe', $standard->id());
        self::assertSame('Probe', $standard->name());
        self::assertSame('0.1.0', $standard->version());
        self::assertNotSame('', $standard->specification());
    }

    public function test_supports_nothing_and_migrate_is_a_safe_no_op(): void
    {
        $standard = new ProbeStandard(new ProbeModule());

        self::assertFalse($standard->supports('anything'));

        $standard->migrate('0.0.9');
    }

    public function test_discover_delegates_to_the_owning_module(): void
    {
        $module = new ProbeModule();
        $standard = new ProbeStandard($module);

        self::assertEquals($module->discover(), $standard->discover());
    }

    public function test_validate_delegates_to_the_owning_module_using_its_own_discovered_resource(): void
    {
        $module = new ProbeModule();
        $standard = new ProbeStandard($module);

        $result = $standard->validate();

        self::assertSame(ValidationStatus::Pass, $result->status);
        self::assertSame('probe-fixture', $result->resourceId);
    }

    public function test_generate_delegates_to_the_owning_module(): void
    {
        $module = new ProbeModule();
        $standard = new ProbeStandard($module);

        self::assertSame($module->generate(), $standard->generate());
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
        $standard = new ProbeStandard(new ProbeModule());

        $this->expectException(ModuleException::class);

        $standard->$method();
    }
}
