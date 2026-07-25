<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Services;

use Brain\Monkey\Actions;
use Mockery;
use OxyAI\Contracts\DiscoveryInterface;
use OxyAI\Contracts\GeneratorInterface;
use OxyAI\Contracts\ValidatorInterface;
use OxyAI\DTO\ChangeType;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\DTO\NotificationPriority;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;
use OxyAI\Repositories\FileRepository;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\MonitoringService;
use OxyAI\Services\ValidationService;
use OxyAI\Tests\Unit\Support\InMemoryFilesystem;
use OxyAI\Tests\Unit\TestCase;

final class MonitoringServiceTest extends TestCase
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

    private function makeGeneration(): GenerationService
    {
        return new GenerationService(
            new ValidationService(),
            new DiscoveryService(),
            new FileRepository('/base', new InMemoryFilesystem())
        );
    }

    public function test_scan_returns_nothing_before_monitoring_has_started(): void
    {
        $discovery = new DiscoveryService();
        $service = new MonitoringService($discovery, new ValidationService(), $this->makeGeneration());

        self::assertFalse($service->isActive());
        self::assertSame([], $service->scan());
    }

    public function test_start_establishes_a_baseline_and_scan_detects_no_change_when_nothing_changed(): void
    {
        $resource = $this->makeResource();

        $provider = Mockery::mock(DiscoveryInterface::class);
        $provider->allows('discover')->andReturn([$resource]);

        $discovery = new DiscoveryService();
        $discovery->registerProvider('robots', $provider);

        $service = new MonitoringService($discovery, new ValidationService(), $this->makeGeneration());

        $service->start();

        self::assertTrue($service->isActive());
        self::assertSame(1, $service->resourcesTracked());
        self::assertNotNull($service->lastCheckedAt());
        self::assertSame([], $service->scan());
        self::assertSame([], $service->events());
    }

    public function test_scan_detects_a_modified_resource_and_triggers_revalidation_and_notification(): void
    {
        $provider = Mockery::mock(DiscoveryInterface::class);
        $provider->allows('discover')->andReturn(
            [$this->makeResource('healthy')],
            [$this->makeResource('degraded')]
        );

        $discovery = new DiscoveryService();
        $discovery->registerProvider('robots', $provider);

        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->allows('validate')->andReturn(
            new ValidationResult('robots-txt', 'robots', ValidationStatus::Warning, 'degraded health', 0.1)
        );

        $validation = new ValidationService();
        $validation->registerValidator('robots', $validator);

        $service = new MonitoringService($discovery, $validation, $this->makeGeneration());
        $service->start();

        Actions\expectDone('oxy_ai_resource_changed')->once();
        Actions\expectDone('oxy_ai_notification_sent')->once();

        $events = $service->scan();

        self::assertCount(1, $events);
        self::assertSame(ChangeType::Modified, $events[0]->changeType);
        self::assertSame(NotificationPriority::Medium, $events[0]->priority);
        self::assertCount(1, $events[0]->results);
        self::assertSame($events, $service->events());
    }

    public function test_scan_detects_a_newly_appeared_resource_as_created(): void
    {
        $provider = Mockery::mock(DiscoveryInterface::class);
        $provider->allows('discover')->andReturn([], [$this->makeResource()]);

        $discovery = new DiscoveryService();
        $discovery->registerProvider('robots', $provider);

        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->allows('validate')->andReturn(
            new ValidationResult('robots-txt', 'robots', ValidationStatus::Pass, 'ok', 0.1)
        );

        $validation = new ValidationService();
        $validation->registerValidator('robots', $validator);

        $service = new MonitoringService($discovery, $validation, $this->makeGeneration());
        $service->start();

        $events = $service->scan();

        self::assertCount(1, $events);
        self::assertSame(ChangeType::Created, $events[0]->changeType);
        self::assertSame(NotificationPriority::Informational, $events[0]->priority);
    }

    public function test_scan_detects_a_removed_resource_as_deleted_with_critical_priority(): void
    {
        $provider = Mockery::mock(DiscoveryInterface::class);
        $provider->allows('discover')->andReturn([$this->makeResource()], []);

        $discovery = new DiscoveryService();
        $discovery->registerProvider('robots', $provider);

        $service = new MonitoringService($discovery, new ValidationService(), $this->makeGeneration());
        $service->start();

        Actions\expectDone('oxy_ai_notification_sent')->once();

        $events = $service->scan();

        self::assertCount(1, $events);
        self::assertSame(ChangeType::Deleted, $events[0]->changeType);
        self::assertSame(NotificationPriority::Critical, $events[0]->priority);
        self::assertSame('robots-txt', $events[0]->resourceId);
        self::assertSame(0, $service->resourcesTracked());
    }

    public function test_scan_detects_a_generated_content_change_even_when_metadata_is_identical(): void
    {
        $resource = $this->makeResource();

        $provider = Mockery::mock(DiscoveryInterface::class);
        $provider->allows('discover')->andReturn([$resource]);

        $discovery = new DiscoveryService();
        $discovery->registerProvider('robots', $provider);

        $generator = Mockery::mock(GeneratorInterface::class);
        $generator->allows('generate')->andReturn('User-agent: *\nAllow: /\n', 'User-agent: *\nDisallow: /\n');

        $generation = $this->makeGeneration();
        $generation->registerGenerator('robots', $generator);

        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->allows('validate')->andReturn(
            new ValidationResult('robots-txt', 'robots', ValidationStatus::Pass, 'ok', 0.1)
        );

        $validation = new ValidationService();
        $validation->registerValidator('robots', $validator);

        $service = new MonitoringService($discovery, $validation, $generation);
        $service->start();

        $events = $service->scan();

        self::assertCount(1, $events);
        self::assertSame(ChangeType::Modified, $events[0]->changeType);
    }

    public function test_stop_prevents_further_scans_until_started_again(): void
    {
        $resource = $this->makeResource();

        $provider = Mockery::mock(DiscoveryInterface::class);
        $provider->allows('discover')->andReturn([$resource], [$this->makeResource('degraded')]);

        $discovery = new DiscoveryService();
        $discovery->registerProvider('robots', $provider);

        $service = new MonitoringService($discovery, new ValidationService(), $this->makeGeneration());
        $service->start();
        $service->stop();

        self::assertFalse($service->isActive());
        self::assertSame([], $service->scan());
    }

    public function test_reset_clears_active_state_baseline_and_events(): void
    {
        $resource = $this->makeResource();

        $provider = Mockery::mock(DiscoveryInterface::class);
        $provider->allows('discover')->andReturn([$resource]);

        $discovery = new DiscoveryService();
        $discovery->registerProvider('robots', $provider);

        $service = new MonitoringService($discovery, new ValidationService(), $this->makeGeneration());
        $service->start();
        $service->reset();

        self::assertFalse($service->isActive());
        self::assertSame(0, $service->resourcesTracked());
        self::assertNull($service->lastCheckedAt());
        self::assertSame([], $service->events());
    }
}
