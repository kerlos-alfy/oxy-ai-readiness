<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\Probe;

use OxyAI\Core\Application;
use OxyAI\Core\Container;
use OxyAI\Core\CoreServiceProvider;
use OxyAI\Core\ModuleRegistry;
use OxyAI\Core\StandardsRegistry;
use OxyAI\Modules\Probe\ProbeServiceProvider;
use OxyAI\Tests\Unit\TestCase;

final class ProbeServiceProviderTest extends TestCase
{
    public function test_register_registers_the_probe_module_and_standard(): void
    {
        $app = new Application(new Container());
        (new CoreServiceProvider($app))->register();

        $provider = new ProbeServiceProvider($app);
        $provider->register();

        self::assertTrue($app->make(ModuleRegistry::class)->has('probe'));
        self::assertTrue($app->make(StandardsRegistry::class)->has('probe'));
    }

    public function test_boot_boots_the_probe_module(): void
    {
        $app = new Application(new Container());
        (new CoreServiceProvider($app))->register();

        $provider = new ProbeServiceProvider($app);
        $provider->register();
        $provider->boot();

        self::assertTrue($app->make(ModuleRegistry::class)->isBooted('probe'));
    }
}
