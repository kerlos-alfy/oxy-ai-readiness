<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Core;

use Brain\Monkey\Actions;
use Mockery;
use OxyAI\Contracts\StandardInterface;
use OxyAI\Core\StandardsRegistry;
use OxyAI\Exceptions\ModuleException;
use OxyAI\Tests\Unit\TestCase;

final class StandardsRegistryTest extends TestCase
{
    private function makeStandard(string $id): StandardInterface&Mockery\MockInterface
    {
        $standard = Mockery::mock(StandardInterface::class);
        $standard->allows('id')->andReturn($id);

        return $standard;
    }

    public function test_register_fires_the_registered_event(): void
    {
        $standard = $this->makeStandard('probe');

        Actions\expectDone('oxy_ai_standard_registered')->once();

        $registry = new StandardsRegistry();
        $registry->register($standard);

        self::assertTrue($registry->has('probe'));
        self::assertTrue($registry->isEnabled('probe'));
        self::assertSame($standard, $registry->get('probe'));
    }

    public function test_register_rejects_a_duplicate_id(): void
    {
        $registry = new StandardsRegistry();
        $registry->register($this->makeStandard('probe'));

        $this->expectException(ModuleException::class);

        $registry->register($this->makeStandard('probe'));
    }

    public function test_disable_then_enable_fire_their_events_and_only_once_each(): void
    {
        Actions\expectDone('oxy_ai_standard_disabled')->once();
        Actions\expectDone('oxy_ai_standard_enabled')->once();

        $registry = new StandardsRegistry();
        $registry->register($this->makeStandard('probe'));

        $registry->disable('probe');
        $registry->disable('probe');
        self::assertFalse($registry->isEnabled('probe'));

        $registry->enable('probe');
        $registry->enable('probe');
        self::assertTrue($registry->isEnabled('probe'));
    }

    public function test_get_throws_for_an_unregistered_id(): void
    {
        $registry = new StandardsRegistry();

        $this->expectException(ModuleException::class);

        $registry->get('missing');
    }

    public function test_all_returns_every_registered_standard_keyed_by_id(): void
    {
        $probe = $this->makeStandard('probe');
        $other = $this->makeStandard('other');

        $registry = new StandardsRegistry();
        $registry->register($probe);
        $registry->register($other);

        self::assertSame(['probe' => $probe, 'other' => $other], $registry->all());
    }
}
