<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Routes;

use Brain\Monkey\Functions;
use OxyAI\Core\Application;
use OxyAI\Core\Container;
use OxyAI\Repositories\FileRepository;
use OxyAI\Services\AuditService;
use OxyAI\Services\AutoFixService;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\RecommendationService;
use OxyAI\Services\ScoringService;
use OxyAI\Services\ValidationService;
use OxyAI\Tests\Unit\Support\InMemoryFilesystem;
use OxyAI\Tests\Unit\TestCase;

final class ApiRoutesTest extends TestCase
{
    public function test_registers_every_engine_and_robots_rest_route(): void
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
        $app->singleton(AuditService::class, static function () use ($app): AuditService {
            return new AuditService(
                $app->make(DiscoveryService::class),
                $app->make(ValidationService::class),
                $app->make(ScoringService::class)
            );
        });
        $app->singleton(RecommendationService::class, static function () use ($app): RecommendationService {
            return new RecommendationService($app->make(GenerationService::class));
        });
        $app->singleton(AutoFixService::class, static function () use ($app): AutoFixService {
            return new AutoFixService(
                $app->make(GenerationService::class),
                $app->make(ValidationService::class),
                $app->make(DiscoveryService::class)
            );
        });

        $expectedGetRoutes = [
            '/discovery', '/discovery/map', '/discovery/resources',
            '/validation', '/generation', '/generation/preview', '/score',
            '/robots', '/robots/preview', '/audit', '/recommendations', '/autofix',
        ];
        $expectedPostRoutes = [
            '/validation/run', '/generation/publish', '/generation/rollback',
            '/robots/save', '/robots/validate', '/robots/reset', '/audit/start',
            '/recommendations/generate', '/autofix/run', '/autofix/rollback',
        ];

        Functions\expect('register_rest_route')
            ->times(count($expectedGetRoutes) + count($expectedPostRoutes))
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
