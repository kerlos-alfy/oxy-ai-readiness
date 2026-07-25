<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Core;

use OxyAI\Core\Application;
use OxyAI\Core\Container;
use OxyAI\Tests\Unit\TestCase;

final class ApplicationTest extends TestCase
{
    public function test_is_not_booted_until_mark_booted_is_called(): void
    {
        $app = new Application(new Container());

        self::assertFalse($app->isBooted());

        $app->markBooted();

        self::assertTrue($app->isBooted());
    }

    public function test_container_returns_the_injected_container(): void
    {
        $container = new Container();
        $app = new Application($container);

        self::assertSame($container, $app->container());
    }

    public function test_bind_singleton_has_and_make_delegate_to_the_container(): void
    {
        $app = new Application(new Container());

        self::assertFalse($app->has('service'));

        $app->singleton('service', static fn (): object => new \stdClass());

        self::assertTrue($app->has('service'));
        self::assertSame($app->make('service'), $app->make('service'));
    }
}
