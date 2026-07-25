<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\OAuthDiscovery;

use OxyAI\Core\Application;
use OxyAI\Core\Config;
use OxyAI\Core\Container;
use OxyAI\Core\CoreServiceProvider;
use OxyAI\Core\ModuleRegistry;
use OxyAI\Core\StandardsRegistry;
use OxyAI\Modules\OAuthDiscovery\OAuthDiscoveryServiceProvider;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\ValidationService;
use OxyAI\Tests\Unit\TestCase;

final class OAuthDiscoveryServiceProviderTest extends TestCase
{
    private function makeApp(): Application
    {
        $app = new Application(new Container());
        $app->singleton(Config::class, static fn (): Config => new Config('0.1.0', '/plugin.php'));
        (new CoreServiceProvider($app))->register();

        return $app;
    }

    public function test_register_registers_the_module_all_three_generators_and_all_three_standards(): void
    {
        $app = $this->makeApp();

        $provider = new OAuthDiscoveryServiceProvider($app);
        $provider->register();

        self::assertTrue($app->make(ModuleRegistry::class)->has('oauth-discovery'));
        self::assertTrue($app->make(ValidationService::class)->has('oauth-discovery'));

        $standards = $app->make(StandardsRegistry::class);
        self::assertTrue($standards->has('openid-configuration'));
        self::assertTrue($standards->has('oauth-authorization-server'));
        self::assertTrue($standards->has('oauth-protected-resource'));

        $generation = $app->make(GenerationService::class);
        self::assertTrue($generation->has('openid-configuration'));
        self::assertTrue($generation->has('oauth-authorization-server'));
        self::assertTrue($generation->has('oauth-protected-resource'));

        $resources = $app->make(DiscoveryService::class)->resources();
        self::assertCount(3, $resources);
    }

    public function test_boot_boots_the_oauth_discovery_module(): void
    {
        $app = $this->makeApp();

        $provider = new OAuthDiscoveryServiceProvider($app);
        $provider->register();
        $provider->boot();

        self::assertTrue($app->make(ModuleRegistry::class)->isBooted('oauth-discovery'));
    }
}
