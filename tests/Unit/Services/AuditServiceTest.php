<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Services;

use Brain\Monkey\Actions;
use Mockery;
use OxyAI\Contracts\DiscoveryInterface;
use OxyAI\Contracts\ValidatorInterface;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\DTO\ScanType;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;
use OxyAI\Services\AuditService;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\ScoringService;
use OxyAI\Services\ValidationService;
use OxyAI\Tests\Unit\TestCase;

final class AuditServiceTest extends TestCase
{
    private function makeResource(string $id): DiscoveredResource
    {
        return new DiscoveredResource(
            id: $id,
            type: 'internal-fixture',
            location: 'internal://' . $id,
            status: 'active',
            version: '0.1.0',
            module: 'fixture',
            health: 'healthy',
            dependencies: [],
            source: 'fixture',
            lastChecked: '2026-07-25T00:00:00+00:00'
        );
    }

    private function makeAuditService(): AuditService
    {
        $provider = Mockery::mock(DiscoveryInterface::class);
        $provider->allows('discover')->andReturn([$this->makeResource('fixture-a')]);

        $discovery = new DiscoveryService();
        $discovery->registerProvider('fixture', $provider);

        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->allows('validate')->andReturn(
            new ValidationResult('fixture-a', 'fixture', ValidationStatus::Pass, 'ok', 0.1)
        );

        $validation = new ValidationService();
        $validation->registerValidator('fixture', $validator);

        return new AuditService($discovery, $validation, new ScoringService());
    }

    public function test_scan_ties_discovery_validation_and_scoring_into_a_structured_report(): void
    {
        Actions\expectDone('oxy_ai_audit_started')->once();
        Actions\expectDone('oxy_ai_audit_completed')->once();

        $service = $this->makeAuditService();
        $report = $service->scan(ScanType::Quick);

        self::assertSame(ScanType::Quick, $report->scanType);
        self::assertCount(1, $report->results);
        self::assertSame(1, $report->summary['pass']);
        self::assertSame(0, $report->summary['fail']);
        self::assertSame(100.0, $report->score->score);
    }

    public function test_last_report_reflects_the_most_recent_scan(): void
    {
        $service = $this->makeAuditService();

        self::assertNull($service->lastReport());

        $report = $service->scan(ScanType::Quick);

        self::assertSame($report, $service->lastReport());
    }

    /**
     * @return iterable<string, array{0: ScanType}>
     */
    public static function scanTypeProvider(): iterable
    {
        yield 'quick' => [ScanType::Quick];
        yield 'full' => [ScanType::Full];
        yield 'deep' => [ScanType::Deep];
        yield 'developer' => [ScanType::Developer];
    }

    /**
     * @dataProvider scanTypeProvider
     */
    public function test_every_scan_type_executes_within_its_documented_performance_target(ScanType $type): void
    {
        $service = $this->makeAuditService();

        $report = $service->scan($type);

        // docs/06-Audit-Engine.md: Quick <5s, Full <20s, Deep <60s;
        // Developer has no documented ceiling, so it inherits Deep's.
        $limitMs = match ($type) {
            ScanType::Quick => 5000.0,
            ScanType::Full => 20000.0,
            ScanType::Deep, ScanType::Developer => 60000.0,
        };

        self::assertLessThan($limitMs, $report->durationMs);
    }
}
