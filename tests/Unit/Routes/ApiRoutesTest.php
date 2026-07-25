<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Routes;

use Brain\Monkey\Functions;
use OxyAI\Core\Application;
use OxyAI\Core\Container;
use OxyAI\Repositories\FileRepository;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\ScoringService;
use OxyAI\Services\ValidationService;
use OxyAI\Tests\Unit\Support\InMemoryFilesystem;
use OxyAI\Tests\Unit\TestCase;

final class ApiRoutesTest extends TestCase
{
    public function test_registers_the_discovery_validation_generation_and_score_rest_routes(): void
    {
        $app = new Application(new Container());
        $app->singleton(DiscoveryService::class, static fn (): DiscoveryService => new DiscoveryService());
        $app->singleton(ValidationService::class, static fn (): ValidationService => new ValidationService());
        $app->singleton(GenerationService::class, static function (): GenerationService {
            return new GenerationService(
                new ValidationService(),
                new DiscoveryService(),
                new FileRepository('/base', new InMemoryFilesystem())
            );
        });
        $app->singleton(ScoringService::class, static fn (): ScoringService => new ScoringService());

        $expectedGetRoutes = [
            '/discovery', '/discovery/map', '/discovery/resources',
            '/validation', '/generation', '/generation/preview', '/score',
        ];
        $expectedPostRoutes = ['/validation/run', '/generation/publish', '/generation/rollback'];

        Functions\expect('register_rest_route')
            ->times(10)
            ->withArgs(
                static function (
                    string $namespace,
                    string $route,
                    array $args
                ) use (
                    $expectedGetRoutes,
                    $expectedPostRoutes
                ): bool {
                    if ($namespace !== 'oxy-ai/v1' || !isset($args['callback'], $args['permission_callback'])) {
                        return false;
                    }

                    if (in_array($route, $expectedPostRoutes, true)) {
                        return $args['methods'] === 'POST';
                    }

                    return in_array($route, $expectedGetRoutes, true) && $args['methods'] === 'GET';
                }
            );

        $registerRoutes = require dirname(__DIR__, 3) . '/routes/api.php';
        $registerRoutes($app);

        $this->expectNotToPerformAssertions();
    }
}
