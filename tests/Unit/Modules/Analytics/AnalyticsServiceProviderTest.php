<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\Analytics;

use OxyAI\Core\Application;
use OxyAI\Core\Config;
use OxyAI\Core\Container;
use OxyAI\Core\CoreServiceProvider;
use OxyAI\Core\ModuleRegistry;
use OxyAI\Core\StandardsRegistry;
use OxyAI\Modules\Analytics\AnalyticsServiceProvider;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\ValidationService;
use OxyAI\Tests\Unit\TestCase;

final class AnalyticsServiceProviderTest extends TestCase
{
    private function makeApp(): Application
    {
        $app = new Application(new Container());
        $app->singleton(Config::class, static fn (): Config => new Config('0.1.0', '/plugin.php'));
        (new CoreServiceProvider($app))->register();

        return $app;
    }

    public function test_register_registers_the_analytics_module_everywhere_it_participates(): void
    {
        $app = $this->makeApp();

        $provider = new AnalyticsServiceProvider($app);
        $provider->register();

        self::assertTrue($app->make(ModuleRegistry::class)->has('analytics'));
        self::assertTrue($app->make(ValidationService::class)->has('analytics'));
        self::assertTrue($app->make(GenerationService::class)->has('analytics'));

        $resources = $app->make(DiscoveryService::class)->resources();
        self::assertCount(1, $resources);
        self::assertSame('analytics-summary', $resources[0]->id);
    }

    public function test_register_does_not_register_a_standard(): void
    {
        $app = $this->makeApp();

        $provider = new AnalyticsServiceProvider($app);
        $provider->register();

        self::assertFalse($app->make(StandardsRegistry::class)->has('analytics'));
    }

    public function test_boot_boots_the_analytics_module(): void
    {
        $app = $this->makeApp();

        $provider = new AnalyticsServiceProvider($app);
        $provider->register();
        $provider->boot();

        self::assertTrue($app->make(ModuleRegistry::class)->isBooted('analytics'));
    }
}
