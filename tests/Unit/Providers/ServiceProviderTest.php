<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Providers;

use OxyAI\Core\Application;
use OxyAI\Core\Container;
use OxyAI\Providers\ServiceProvider;
use OxyAI\Tests\Unit\TestCase;

final class ServiceProviderTest extends TestCase
{
    public function test_register_is_called_and_binds_into_the_application(): void
    {
        $app = new Application(new Container());

        $provider = new class ($app) extends ServiceProvider {
            public function register(): void
            {
                $this->app->singleton('example', static fn (): string => 'bound');
            }
        };

        $provider->register();

        self::assertSame('bound', $app->make('example'));
    }

    public function test_boot_defaults_to_a_no_op(): void
    {
        $app = new Application(new Container());

        $provider = new class ($app) extends ServiceProvider {
            public function register(): void
            {
            }
        };

        $provider->boot();

        $this->expectNotToPerformAssertions();
    }
}
