<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\Probe;

use OxyAI\Exceptions\ModuleException;
use OxyAI\Modules\Probe\ProbeStandard;
use OxyAI\Tests\Unit\TestCase;

final class ProbeStandardTest extends TestCase
{
    public function test_exposes_its_identity(): void
    {
        $standard = new ProbeStandard();

        self::assertSame('probe', $standard->id());
        self::assertSame('Probe', $standard->name());
        self::assertSame('0.1.0', $standard->version());
        self::assertNotSame('', $standard->specification());
    }

    public function test_supports_nothing_and_migrate_is_a_safe_no_op(): void
    {
        $standard = new ProbeStandard();

        self::assertFalse($standard->supports('anything'));

        $standard->migrate('0.0.9');
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function delegateMethodProvider(): iterable
    {
        yield 'discover' => ['discover'];
        yield 'generate' => ['generate'];
        yield 'validate' => ['validate'];
        yield 'score' => ['score'];
        yield 'monitor' => ['monitor'];
        yield 'report' => ['report'];
    }

    /**
     * @dataProvider delegateMethodProvider
     */
    public function test_delegate_methods_throw_since_no_engine_is_registered_yet(string $method): void
    {
        $standard = new ProbeStandard();

        $this->expectException(ModuleException::class);

        $standard->$method();
    }
}
