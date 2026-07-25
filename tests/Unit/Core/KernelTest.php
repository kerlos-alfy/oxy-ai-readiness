<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Core;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use OxyAI\Core\Application;
use OxyAI\Core\Bootstrap;
use OxyAI\Core\Container;
use OxyAI\Core\Hooks;
use OxyAI\Core\Kernel;
use OxyAI\Tests\Unit\TestCase;

final class KernelTest extends TestCase
{
    public function test_register_adds_a_plugins_loaded_action_pointing_at_boot(): void
    {
        $app = new Application(new Container());
        $hooks = new Hooks();
        $kernel = new Kernel(new Bootstrap($app), $hooks);

        Functions\expect('add_action')
            ->once()
            ->with('plugins_loaded', [$kernel, 'boot'], 10, 1);

        $kernel->register();

        self::assertSame('plugins_loaded', $hooks->registeredActions()[0]['hook']);
    }

    public function test_boot_delegates_to_bootstrap_and_marks_the_application_booted(): void
    {
        $app = new Application(new Container());
        $kernel = new Kernel(new Bootstrap($app), new Hooks());

        Actions\expectDone('oxy_ai_ready')->once();

        $kernel->boot();

        self::assertTrue($app->isBooted());
    }
}
