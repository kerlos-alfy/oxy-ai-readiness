<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Core;

use Brain\Monkey\Actions;
use OxyAI\Core\Application;
use OxyAI\Core\Container;
use OxyAI\Core\Hooks;
use OxyAI\Core\RestServiceProvider;
use OxyAI\Tests\Unit\TestCase;

final class RestServiceProviderTest extends TestCase
{
    public function test_boot_registers_a_rest_api_init_action(): void
    {
        $app = new Application(new Container());
        $app->singleton(Hooks::class, static fn (): Hooks => new Hooks());

        $provider = new RestServiceProvider($app);
        $provider->boot();

        self::assertTrue(Actions\has('rest_api_init'));
    }

    public function test_register_is_a_safe_no_op(): void
    {
        $app = new Application(new Container());
        $app->singleton(Hooks::class, static fn (): Hooks => new Hooks());

        $provider = new RestServiceProvider($app);
        $provider->register();

        $this->expectNotToPerformAssertions();
    }
}
