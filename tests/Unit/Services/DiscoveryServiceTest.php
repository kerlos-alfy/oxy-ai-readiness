<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Services;

use Brain\Monkey\Actions;
use Mockery;
use OxyAI\Contracts\DiscoveryInterface;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\Exceptions\ModuleException;
use OxyAI\Services\DiscoveryService;
use OxyAI\Tests\Unit\TestCase;

final class DiscoveryServiceTest extends TestCase
{
    private function makeResource(string $id): DiscoveredResource
    {
        return new DiscoveredResource(
            id: $id,
            type: 'internal-fixture',
            location: 'internal://' . $id,
            status: 'active',
            version: '0.1.0',
            module: 'probe',
            health: 'healthy',
            dependencies: [],
            source: 'fixture',
            lastChecked: '2026-07-25T00:00:00+00:00'
        );
    }

    private function makeProvider(DiscoveredResource ...$resources): DiscoveryInterface&Mockery\MockInterface
    {
        $provider = Mockery::mock(DiscoveryInterface::class);
        $provider->allows('discover')->andReturn($resources);

        return $provider;
    }

    public function test_scan_builds_the_map_from_every_registered_provider_and_fires_events(): void
    {
        Actions\expectDone('oxy_ai_discovery_started')->once();
        Actions\expectDone('oxy_ai_resource_discovered')->twice();
        Actions\expectDone('oxy_ai_discovery_finished')->once();

        $service = new DiscoveryService();
        $service->registerProvider('probe', $this->makeProvider($this->makeResource('a')));
        $service->registerProvider('other', $this->makeProvider($this->makeResource('b')));

        $service->scan();

        self::assertSame(['a', 'b'], array_keys($service->map()));
    }

    public function test_register_provider_rejects_a_duplicate_module_id(): void
    {
        $service = new DiscoveryService();
        $service->registerProvider('probe', $this->makeProvider());

        $this->expectException(ModuleException::class);

        $service->registerProvider('probe', $this->makeProvider());
    }

    public function test_map_lazily_scans_on_first_access(): void
    {
        $service = new DiscoveryService();
        $service->registerProvider('probe', $this->makeProvider($this->makeResource('probe-fixture')));

        self::assertSame(['probe-fixture'], array_keys($service->map()));
    }

    public function test_resources_returns_the_map_values_as_a_list(): void
    {
        $service = new DiscoveryService();
        $service->registerProvider('probe', $this->makeProvider($this->makeResource('probe-fixture')));

        $resources = $service->resources();

        self::assertCount(1, $resources);
        self::assertSame('probe-fixture', $resources[0]->id);
    }

    public function test_reset_forces_a_fresh_scan_on_next_access(): void
    {
        $provider = $this->makeProvider($this->makeResource('probe-fixture'));

        $service = new DiscoveryService();
        $service->registerProvider('probe', $provider);
        $service->map();

        $service->reset();

        Actions\expectDone('oxy_ai_discovery_started')->once();

        $service->map();

        $this->expectNotToPerformAssertions();
    }
}
