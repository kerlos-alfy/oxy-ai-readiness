<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\ApiCatalog;

use OxyAI\Core\Application;
use OxyAI\Core\Config;
use OxyAI\Core\Container;
use OxyAI\Core\CoreServiceProvider;
use OxyAI\Core\ModuleRegistry;
use OxyAI\Core\StandardsRegistry;
use OxyAI\Modules\ApiCatalog\ApiCatalogServiceProvider;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\ValidationService;
use OxyAI\Tests\Unit\TestCase;

final class ApiCatalogServiceProviderTest extends TestCase
{
    private function makeApp(): Application
    {
        $app = new Application(new Container());
        $app->singleton(Config::class, static fn (): Config => new Config('0.1.0', '/plugin.php'));
        (new CoreServiceProvider($app))->register();

        return $app;
    }

    public function test_register_registers_the_api_catalog_module_everywhere_it_participates(): void
    {
        $app = $this->makeApp();

        $provider = new ApiCatalogServiceProvider($app);
        $provider->register();

        self::assertTrue($app->make(ModuleRegistry::class)->has('api-catalog'));
        self::assertTrue($app->make(StandardsRegistry::class)->has('api-catalog'));
        self::assertTrue($app->make(ValidationService::class)->has('api-catalog'));
        self::assertTrue($app->make(GenerationService::class)->has('api-catalog'));

        $resources = $app->make(DiscoveryService::class)->resources();
        self::assertCount(1, $resources);
        self::assertSame('api-catalog', $resources[0]->id);
    }

    public function test_boot_boots_the_api_catalog_module(): void
    {
        $app = $this->makeApp();

        $provider = new ApiCatalogServiceProvider($app);
        $provider->register();
        $provider->boot();

        self::assertTrue($app->make(ModuleRegistry::class)->isBooted('api-catalog'));
    }
}
