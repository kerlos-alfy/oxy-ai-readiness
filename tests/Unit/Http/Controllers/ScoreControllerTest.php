<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Http\Controllers;

use Brain\Monkey\Functions;
use Mockery;
use OxyAI\Contracts\DiscoveryInterface;
use OxyAI\Contracts\ValidatorInterface;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;
use OxyAI\Http\Controllers\ScoreController;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\ScoringService;
use OxyAI\Services\ValidationService;
use OxyAI\Tests\Unit\TestCase;
use WP_REST_Request;

final class ScoreControllerTest extends TestCase
{
    public function test_authorize_checks_the_manage_options_capability(): void
    {
        Functions\expect('current_user_can')->once()->with('manage_options')->andReturn(true);

        $controller = new ScoreController(new DiscoveryService(), new ValidationService(), new ScoringService());

        self::assertTrue($controller->authorize());
    }

    public function test_index_chains_discovery_validation_and_scoring(): void
    {
        $resource = new DiscoveredResource(
            id: 'probe-fixture',
            type: 'internal-fixture',
            location: 'internal://probe',
            status: 'active',
            version: '0.1.0',
            module: 'probe',
            health: 'healthy',
            dependencies: [],
            source: 'fixture',
            lastChecked: '2026-07-25T00:00:00+00:00'
        );

        $discoveryProvider = Mockery::mock(DiscoveryInterface::class);
        $discoveryProvider->allows('discover')->andReturn([$resource]);
        $discovery = new DiscoveryService();
        $discovery->registerProvider('probe', $discoveryProvider);

        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->allows('validate')->andReturn(
            new ValidationResult('probe-fixture', 'probe', ValidationStatus::Pass, 'ok', 0.1)
        );
        $validation = new ValidationService();
        $validation->registerValidator('probe', $validator);

        $controller = new ScoreController($discovery, $validation, new ScoringService());

        $response = $controller->index(new WP_REST_Request());

        self::assertSame(200, $response->get_status());
        $data = $response->get_data();
        self::assertTrue($data['success']);
        self::assertSame(100.0, $data['data']['score']);
        self::assertSame('A+', $data['data']['grade']);
    }

    public function test_index_scores_zero_when_nothing_has_been_discovered(): void
    {
        $controller = new ScoreController(new DiscoveryService(), new ValidationService(), new ScoringService());

        $response = $controller->index(new WP_REST_Request());

        self::assertSame(0.0, $response->get_data()['data']['score']);
        self::assertSame('F', $response->get_data()['data']['grade']);
    }
}
