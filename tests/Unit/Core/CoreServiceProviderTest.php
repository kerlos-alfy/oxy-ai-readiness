<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Core;

use OxyAI\Core\Application;
use OxyAI\Core\Container;
use OxyAI\Core\CoreServiceProvider;
use OxyAI\Core\ModuleRegistry;
use OxyAI\Core\StandardsRegistry;
use OxyAI\Tests\Unit\TestCase;

final class CoreServiceProviderTest extends TestCase
{
    public function test_register_binds_module_and_standards_registries_as_singletons(): void
    {
        $app = new Application(new Container());
        $provider = new CoreServiceProvider($app);

        $provider->register();

        self::assertInstanceOf(ModuleRegistry::class, $app->make(ModuleRegistry::class));
        self::assertSame($app->make(ModuleRegistry::class), $app->make(ModuleRegistry::class));

        self::assertInstanceOf(StandardsRegistry::class, $app->make(StandardsRegistry::class));
        self::assertSame($app->make(StandardsRegistry::class), $app->make(StandardsRegistry::class));
    }
}
