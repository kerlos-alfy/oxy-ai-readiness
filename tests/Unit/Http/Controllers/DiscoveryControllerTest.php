<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Http\Controllers;

use Brain\Monkey\Functions;
use Mockery;
use OxyAI\Contracts\DiscoveryInterface;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\Http\Controllers\DiscoveryController;
use OxyAI\Services\DiscoveryService;
use OxyAI\Tests\Unit\TestCase;
use WP_REST_Request;

final class DiscoveryControllerTest extends TestCase
{
    private function discoveryServiceWithOneFixture(): DiscoveryService
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

        $provider = Mockery::mock(DiscoveryInterface::class);
        $provider->allows('discover')->andReturn([$resource]);

        $discovery = new DiscoveryService();
        $discovery->registerProvider('probe', $provider);

        return $discovery;
    }

    public function test_authorize_checks_the_manage_options_capability(): void
    {
        Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(true);

        $controller = new DiscoveryController(new DiscoveryService());

        self::assertTrue($controller->authorize());
    }

    public function test_index_returns_the_resource_count(): void
    {
        $controller = new DiscoveryController($this->discoveryServiceWithOneFixture());
        $response = $controller->index(new WP_REST_Request());

        self::assertSame(200, $response->get_status());
        self::assertSame(
            ['success' => true, 'data' => ['resources_count' => 1]],
            $response->get_data()
        );
    }

    public function test_map_returns_resources_keyed_by_id_as_arrays(): void
    {
        $controller = new DiscoveryController($this->discoveryServiceWithOneFixture());
        $response = $controller->map(new WP_REST_Request());

        $data = $response->get_data();

        self::assertTrue($data['success']);
        self::assertArrayHasKey('probe-fixture', $data['data']);
        self::assertSame('probe', $data['data']['probe-fixture']['module']);
    }

    public function test_resources_returns_a_list_of_resource_arrays(): void
    {
        $controller = new DiscoveryController($this->discoveryServiceWithOneFixture());
        $response = $controller->resources(new WP_REST_Request());

        $data = $response->get_data();

        self::assertTrue($data['success']);
        self::assertCount(1, $data['data']);
        self::assertSame('probe-fixture', $data['data'][0]['id']);
    }
}
