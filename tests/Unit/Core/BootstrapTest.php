<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Core;

use ArrayObject;
use Brain\Monkey\Actions;
use OxyAI\Core\Application;
use OxyAI\Core\Bootstrap;
use OxyAI\Core\Container;
use OxyAI\Providers\ServiceProvider;
use OxyAI\Tests\Unit\TestCase;

final class BootstrapTest extends TestCase
{
    public function test_run_marks_the_application_booted_and_fires_the_ready_event(): void
    {
        $app = new Application(new Container());

        Actions\expectDone('oxy_ai_ready')->once()->with($app);

        $bootstrap = new Bootstrap($app);
        $bootstrap->run();

        self::assertTrue($app->isBooted());
    }

    public function test_run_is_idempotent_and_fires_the_ready_event_only_once(): void
    {
        $app = new Application(new Container());

        Actions\expectDone('oxy_ai_ready')->once();

        $bootstrap = new Bootstrap($app);
        $bootstrap->run();
        $bootstrap->run();

        self::assertTrue($app->isBooted());
    }

    public function test_run_registers_every_provider_before_booting_any_of_them(): void
    {
        $app = new Application(new Container());
        $calls = new ArrayObject();

        $first = new class ($app, $calls) extends ServiceProvider {
            public function __construct(Application $app, private readonly ArrayObject $calls)
            {
                parent::__construct($app);
            }

            public function register(): void
            {
                $this->calls[] = 'first.register';
            }

            public function boot(): void
            {
                $this->calls[] = 'first.boot';
            }
        };

        $second = new class ($app, $calls) extends ServiceProvider {
            public function __construct(Application $app, private readonly ArrayObject $calls)
            {
                parent::__construct($app);
            }

            public function register(): void
            {
                $this->calls[] = 'second.register';
            }

            public function boot(): void
            {
                $this->calls[] = 'second.boot';
            }
        };

        $bootstrap = new Bootstrap($app, [$first, $second]);
        $bootstrap->run();

        self::assertSame(
            ['first.register', 'second.register', 'first.boot', 'second.boot'],
            $calls->getArrayCopy()
        );
    }
}
