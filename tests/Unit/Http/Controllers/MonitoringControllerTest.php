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
use OxyAI\Http\Controllers\MonitoringController;
use OxyAI\Repositories\FileRepository;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\MonitoringService;
use OxyAI\Services\ValidationService;
use OxyAI\Tests\Unit\Support\InMemoryFilesystem;
use OxyAI\Tests\Unit\TestCase;
use WP_REST_Request;

final class MonitoringControllerTest extends TestCase
{
    private function makeResource(string $health = 'healthy'): DiscoveredResource
    {
        return new DiscoveredResource(
            id: 'robots-txt',
            type: 'robots-txt',
            location: '/robots.txt',
            status: 'active',
            version: '0.1.0',
            module: 'robots',
            health: $health,
            dependencies: [],
            source: 'robots',
            lastChecked: '2026-07-25T00:00:00+00:00'
        );
    }

    private function makeMonitoringService(DiscoveryInterface $provider): MonitoringService
    {
        $discovery = new DiscoveryService();
        $discovery->registerProvider('robots', $provider);

        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->allows('validate')->andReturn(
            new ValidationResult('robots-txt', 'robots', ValidationStatus::Warning, 'degraded', 0.1)
        );

        $validation = new ValidationService();
        $validation->registerValidator('robots', $validator);

        $files = new FileRepository('/base', new InMemoryFilesystem());
        $generation = new GenerationService($validation, $discovery, $files);

        return new MonitoringService($discovery, $validation, $generation);
    }

    public function test_authorize_checks_the_manage_options_capability(): void
    {
        Functions\expect('current_user_can')->once()->with('manage_options')->andReturn(true);

        $provider = Mockery::mock(DiscoveryInterface::class);
        $provider->allows('discover')->andReturn([]);

        self::assertTrue((new MonitoringController($this->makeMonitoringService($provider)))->authorize());
    }

    public function test_status_reflects_inactive_state_before_start(): void
    {
        $provider = Mockery::mock(DiscoveryInterface::class);
        $provider->allows('discover')->andReturn([]);

        $controller = new MonitoringController($this->makeMonitoringService($provider));
        $response = $controller->status(new WP_REST_Request());

        self::assertSame(200, $response->get_status());
        self::assertFalse($response->get_data()['data']['active']);
        self::assertNull($response->get_data()['data']['last_checked_at']);
    }

    public function test_start_arms_monitoring_and_returns_active_status(): void
    {
        $provider = Mockery::mock(DiscoveryInterface::class);
        $provider->allows('discover')->andReturn([$this->makeResource()]);

        $controller = new MonitoringController($this->makeMonitoringService($provider));
        $response = $controller->start(new WP_REST_Request());

        self::assertTrue($response->get_data()['data']['active']);
        self::assertSame(1, $response->get_data()['data']['resources_tracked']);
    }

    public function test_scan_returns_a_detected_change_after_start(): void
    {
        $provider = Mockery::mock(DiscoveryInterface::class);
        $provider->allows('discover')->andReturn(
            [$this->makeResource('healthy')],
            [$this->makeResource('degraded')]
        );

        $controller = new MonitoringController($this->makeMonitoringService($provider));
        $controller->start(new WP_REST_Request());

        $response = $controller->scan(new WP_REST_Request());

        self::assertSame(200, $response->get_status());
        self::assertCount(1, $response->get_data()['data']);
        self::assertSame('modified', $response->get_data()['data'][0]['change_type']);
    }

    public function test_events_reflects_events_recorded_by_a_prior_scan(): void
    {
        $provider = Mockery::mock(DiscoveryInterface::class);
        $provider->allows('discover')->andReturn(
            [$this->makeResource('healthy')],
            [$this->makeResource('degraded')]
        );

        $controller = new MonitoringController($this->makeMonitoringService($provider));
        $controller->start(new WP_REST_Request());
        $controller->scan(new WP_REST_Request());

        $response = $controller->events(new WP_REST_Request());

        self::assertCount(1, $response->get_data()['data']);
    }

    public function test_index_combines_status_and_events(): void
    {
        $provider = Mockery::mock(DiscoveryInterface::class);
        $provider->allows('discover')->andReturn([]);

        $controller = new MonitoringController($this->makeMonitoringService($provider));
        $response = $controller->index(new WP_REST_Request());

        $data = $response->get_data()['data'];
        self::assertArrayHasKey('active', $data);
        self::assertArrayHasKey('events', $data);
    }

    public function test_stop_deactivates_monitoring(): void
    {
        $provider = Mockery::mock(DiscoveryInterface::class);
        $provider->allows('discover')->andReturn([]);

        $controller = new MonitoringController($this->makeMonitoringService($provider));
        $controller->start(new WP_REST_Request());

        $response = $controller->stop(new WP_REST_Request());

        self::assertFalse($response->get_data()['data']['active']);
    }

    public function test_reset_clears_tracked_resources(): void
    {
        $provider = Mockery::mock(DiscoveryInterface::class);
        $provider->allows('discover')->andReturn([$this->makeResource()]);

        $controller = new MonitoringController($this->makeMonitoringService($provider));
        $controller->start(new WP_REST_Request());

        $response = $controller->reset(new WP_REST_Request());

        self::assertFalse($response->get_data()['data']['active']);
        self::assertSame(0, $response->get_data()['data']['resources_tracked']);
    }
}
