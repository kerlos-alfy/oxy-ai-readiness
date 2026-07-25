<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Routes;

use Brain\Monkey\Functions;
use OxyAI\Core\Application;
use OxyAI\Core\Container;
use OxyAI\Services\DiscoveryService;
use OxyAI\Tests\Unit\TestCase;

final class ApiRoutesTest extends TestCase
{
    public function test_registers_the_three_discovery_rest_routes(): void
    {
        $app = new Application(new Container());
        $app->singleton(DiscoveryService::class, static fn (): DiscoveryService => new DiscoveryService());

        Functions\expect('register_rest_route')
            ->times(3)
            ->withArgs(static function (string $namespace, string $route, array $args): bool {
                return $namespace === 'oxy-ai/v1'
                    && in_array($route, ['/discovery', '/discovery/map', '/discovery/resources'], true)
                    && $args['methods'] === 'GET'
                    && isset($args['callback'], $args['permission_callback']);
            });

        $registerRoutes = require dirname(__DIR__, 3) . '/routes/api.php';
        $registerRoutes($app);

        $this->expectNotToPerformAssertions();
    }
}
