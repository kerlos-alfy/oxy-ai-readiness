<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Core;

use OutOfBoundsException;
use OxyAI\Core\Container;
use OxyAI\Tests\Unit\TestCase;

final class ContainerTest extends TestCase
{
    public function test_bind_resolves_a_new_instance_on_every_make(): void
    {
        $container = new Container();
        $container->bind('counter', static function (): object {
            static $count = 0;
            $count++;

            return (object) ['count' => $count];
        });

        self::assertSame(1, $container->make('counter')->count);
        self::assertSame(2, $container->make('counter')->count);
    }

    public function test_singleton_resolves_the_same_instance_every_time(): void
    {
        $container = new Container();
        $container->singleton('service', static fn (): object => new \stdClass());

        $first = $container->make('service');
        $second = $container->make('service');

        self::assertSame($first, $second);
    }

    public function test_has_reflects_whether_an_id_is_bound(): void
    {
        $container = new Container();

        self::assertFalse($container->has('missing'));

        $container->bind('present', static fn (): string => 'value');

        self::assertTrue($container->has('present'));
    }

    public function test_make_throws_when_id_is_unbound(): void
    {
        $container = new Container();

        $this->expectException(OutOfBoundsException::class);

        $container->make('missing');
    }

    public function test_rebinding_an_id_clears_its_cached_singleton_instance(): void
    {
        $container = new Container();
        $container->singleton('service', static fn (): string => 'first');

        self::assertSame('first', $container->make('service'));

        $container->singleton('service', static fn (): string => 'second');

        self::assertSame('second', $container->make('service'));
    }
}
