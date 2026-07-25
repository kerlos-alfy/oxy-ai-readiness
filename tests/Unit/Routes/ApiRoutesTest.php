<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Routes;

use Brain\Monkey\Functions;
use OxyAI\Core\Application;
use OxyAI\Core\Container;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\ValidationService;
use OxyAI\Tests\Unit\TestCase;

final class ApiRoutesTest extends TestCase
{
    public function test_registers_the_discovery_and_validation_rest_routes(): void
    {
        $app = new Application(new Container());
        $app->singleton(DiscoveryService::class, static fn (): DiscoveryService => new DiscoveryService());
        $app->singleton(ValidationService::class, static fn (): ValidationService => new ValidationService());

        $expectedGetRoutes = ['/discovery', '/discovery/map', '/discovery/resources', '/validation'];

        Functions\expect('register_rest_route')
            ->times(5)
            ->withArgs(static function (string $namespace, string $route, array $args) use ($expectedGetRoutes): bool {
                if ($namespace !== 'oxy-ai/v1' || !isset($args['callback'], $args['permission_callback'])) {
                    return false;
                }

                if ($route === '/validation/run') {
                    return $args['methods'] === 'POST';
                }

                return in_array($route, $expectedGetRoutes, true) && $args['methods'] === 'GET';
            });

        $registerRoutes = require dirname(__DIR__, 3) . '/routes/api.php';
        $registerRoutes($app);

        $this->expectNotToPerformAssertions();
    }
}
