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
use OxyAI\Http\Controllers\ValidationController;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\ValidationService;
use OxyAI\Tests\Unit\TestCase;
use WP_REST_Request;

final class ValidationControllerTest extends TestCase
{
    /**
     * @return array{0: DiscoveryService, 1: ValidationService}
     */
    private function makeServices(): array
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

        return [$discovery, $validation];
    }

    public function test_authorize_checks_the_manage_options_capability(): void
    {
        Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(true);

        [$discovery, $validation] = $this->makeServices();
        $controller = new ValidationController($discovery, $validation);

        self::assertTrue($controller->authorize());
    }

    public function test_index_returns_the_registered_validator_count(): void
    {
        [$discovery, $validation] = $this->makeServices();
        $controller = new ValidationController($discovery, $validation);

        $response = $controller->index(new WP_REST_Request());

        self::assertSame(
            ['success' => true, 'data' => ['validators_count' => 1]],
            $response->get_data()
        );
    }

    public function test_run_rejects_a_missing_resource_id(): void
    {
        [$discovery, $validation] = $this->makeServices();
        $controller = new ValidationController($discovery, $validation);

        $response = $controller->run(new WP_REST_Request());

        self::assertSame(400, $response->get_status());
        self::assertFalse($response->get_data()['success']);
    }

    public function test_run_rejects_an_unknown_resource_id(): void
    {
        [$discovery, $validation] = $this->makeServices();
        $controller = new ValidationController($discovery, $validation);

        $request = new WP_REST_Request();
        $request->set_param('resource_id', 'does-not-exist');

        $response = $controller->run($request);

        self::assertSame(404, $response->get_status());
        self::assertFalse($response->get_data()['success']);
    }

    public function test_run_validates_a_known_resource_and_returns_results(): void
    {
        [$discovery, $validation] = $this->makeServices();
        $controller = new ValidationController($discovery, $validation);

        $request = new WP_REST_Request();
        $request->set_param('resource_id', 'probe-fixture');

        $response = $controller->run($request);

        self::assertSame(200, $response->get_status());
        $data = $response->get_data();
        self::assertTrue($data['success']);
        self::assertCount(1, $data['data']);
        self::assertSame('pass', $data['data'][0]['status']);
    }
}
