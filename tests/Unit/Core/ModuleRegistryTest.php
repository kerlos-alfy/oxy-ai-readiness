<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Core;

use Brain\Monkey\Actions;
use Mockery;
use OxyAI\Contracts\ModuleInterface;
use OxyAI\Core\ModuleRegistry;
use OxyAI\Exceptions\ModuleException;
use OxyAI\Tests\Unit\TestCase;

final class ModuleRegistryTest extends TestCase
{
    private function makeModule(string $id): ModuleInterface&Mockery\MockInterface
    {
        $module = Mockery::mock(ModuleInterface::class);
        $module->allows('id')->andReturn($id);

        return $module;
    }

    public function test_register_calls_register_and_fires_the_registered_event(): void
    {
        $module = $this->makeModule('probe');
        $module->expects('register')->once();

        Actions\expectDone('oxy_ai_module_registered')->once();

        $registry = new ModuleRegistry();
        $registry->register($module);

        self::assertTrue($registry->has('probe'));
        self::assertTrue($registry->isEnabled('probe'));
        self::assertFalse($registry->isBooted('probe'));
        self::assertSame($module, $registry->get('probe'));
    }

    public function test_register_rejects_a_duplicate_id(): void
    {
        $module = $this->makeModule('probe');
        $module->allows('register');

        $registry = new ModuleRegistry();
        $registry->register($module);

        $this->expectException(ModuleException::class);

        $registry->register($this->makeModule('probe'));
    }

    public function test_boot_calls_boot_then_init_and_fires_the_booted_event(): void
    {
        $module = $this->makeModule('probe');
        $module->allows('register');
        $module->expects('boot')->once();
        $module->expects('init')->once();

        Actions\expectDone('oxy_ai_module_booted')->once();

        $registry = new ModuleRegistry();
        $registry->register($module);
        $registry->boot('probe');

        self::assertTrue($registry->isBooted('probe'));
    }

    public function test_boot_is_idempotent(): void
    {
        $module = $this->makeModule('probe');
        $module->allows('register');
        $module->expects('boot')->once();
        $module->expects('init')->once();

        $registry = new ModuleRegistry();
        $registry->register($module);
        $registry->boot('probe');
        $registry->boot('probe');

        self::assertTrue($registry->isBooted('probe'));
    }

    public function test_boot_does_nothing_for_a_disabled_module(): void
    {
        $module = $this->makeModule('probe');
        $module->allows('register');
        $module->expects('boot')->never();
        $module->expects('init')->never();

        $registry = new ModuleRegistry();
        $registry->register($module);
        $registry->disable('probe');
        $registry->boot('probe');

        self::assertFalse($registry->isBooted('probe'));
    }

    public function test_disable_then_enable_fire_their_events_and_only_once_each(): void
    {
        $module = $this->makeModule('probe');
        $module->allows('register');

        Actions\expectDone('oxy_ai_module_disabled')->once();
        Actions\expectDone('oxy_ai_module_enabled')->once();

        $registry = new ModuleRegistry();
        $registry->register($module);

        $registry->disable('probe');
        $registry->disable('probe');
        self::assertFalse($registry->isEnabled('probe'));

        $registry->enable('probe');
        $registry->enable('probe');
        self::assertTrue($registry->isEnabled('probe'));
    }

    public function test_disable_un_boots_the_module(): void
    {
        $module = $this->makeModule('probe');
        $module->allows('register');
        $module->allows('boot');
        $module->allows('init');

        $registry = new ModuleRegistry();
        $registry->register($module);
        $registry->boot('probe');

        $registry->disable('probe');

        self::assertFalse($registry->isBooted('probe'));
    }

    public function test_remove_disables_shuts_down_and_forgets_the_module(): void
    {
        $module = $this->makeModule('probe');
        $module->allows('register');
        $module->expects('shutdown')->once();

        $registry = new ModuleRegistry();
        $registry->register($module);

        $registry->remove('probe');

        self::assertFalse($registry->has('probe'));
    }

    public function test_get_and_enable_throw_for_an_unregistered_id(): void
    {
        $registry = new ModuleRegistry();

        $this->expectException(ModuleException::class);

        $registry->get('missing');
    }

    public function test_all_returns_every_registered_module_keyed_by_id(): void
    {
        $probe = $this->makeModule('probe');
        $probe->allows('register');
        $other = $this->makeModule('other');
        $other->allows('register');

        $registry = new ModuleRegistry();
        $registry->register($probe);
        $registry->register($other);

        self::assertSame(['probe' => $probe, 'other' => $other], $registry->all());
    }
}
