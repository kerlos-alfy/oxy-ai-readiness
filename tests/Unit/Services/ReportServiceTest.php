<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Services;

use Brain\Monkey\Actions;
use Mockery;
use OxyAI\Contracts\DiscoveryInterface;
use OxyAI\Contracts\ValidatorInterface;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\DTO\ExportFormat;
use OxyAI\DTO\ScanType;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;
use OxyAI\Repositories\FileRepository;
use OxyAI\Services\AuditService;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\MonitoringService;
use OxyAI\Services\RecommendationService;
use OxyAI\Services\ReportService;
use OxyAI\Services\ScoringService;
use OxyAI\Services\ValidationService;
use OxyAI\Tests\Unit\Support\InMemoryFilesystem;
use OxyAI\Tests\Unit\TestCase;

final class ReportServiceTest extends TestCase
{
    private function makeReportService(): ReportService
    {
        $resource = new DiscoveredResource(
            id: 'robots-txt',
            type: 'robots-txt',
            location: '/robots.txt',
            status: 'active',
            version: '0.1.0',
            module: 'robots',
            health: 'healthy',
            dependencies: [],
            source: 'robots',
            lastChecked: '2026-07-25T00:00:00+00:00'
        );

        $provider = Mockery::mock(DiscoveryInterface::class);
        $provider->allows('discover')->andReturn([$resource]);

        $discovery = new DiscoveryService();
        $discovery->registerProvider('robots', $provider);

        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->allows('validate')->andReturn(
            new ValidationResult('robots-txt', 'robots', ValidationStatus::Fail, 'robots.txt is missing a rule', 0.1)
        );

        $validation = new ValidationService();
        $validation->registerValidator('robots', $validator);

        $audit = new AuditService($discovery, $validation, new ScoringService());

        $files = new FileRepository('/base', new InMemoryFilesystem());
        $generation = new GenerationService($validation, $discovery, $files);
        $recommendation = new RecommendationService($generation);

        $monitoring = new MonitoringService($discovery, $validation, $generation);

        return new ReportService($audit, $recommendation, $monitoring);
    }

    public function test_generate_aggregates_audit_recommendations_and_monitoring_events(): void
    {
        Actions\expectDone('oxy_ai_report_started')->once();
        Actions\expectDone('oxy_ai_report_completed')->once();

        $service = $this->makeReportService();
        $report = $service->generate(ScanType::Quick);

        self::assertSame(ScanType::Quick, $report->auditReport->scanType);
        self::assertCount(1, $report->auditReport->results);
        self::assertCount(1, $report->recommendations);
        self::assertSame('Fix robots-txt', $report->recommendations[0]->title);
        self::assertSame([], $report->monitoringEvents);
        self::assertSame($report, $service->lastReport());
    }

    public function test_generate_defaults_to_a_quick_scan(): void
    {
        $service = $this->makeReportService();
        $report = $service->generate();

        self::assertSame(ScanType::Quick, $report->auditReport->scanType);
    }

    public function test_last_report_is_null_before_any_report_has_been_generated(): void
    {
        self::assertNull($this->makeReportService()->lastReport());
    }

    public function test_export_as_json_round_trips_the_reports_own_array_shape(): void
    {
        $service = $this->makeReportService();
        $report = $service->generate();

        $json = $service->export($report, ExportFormat::Json);

        // assertEquals (not assertSame): JSON has no distinct int/float
        // representation for whole numbers (e.g. a 0.0 score encodes as
        // `0`, decoding back as an int), so a byte-for-byte type match
        // isn't the property being tested here — data preservation is.
        self::assertEquals($report->toArray(), json_decode($json, true));
    }

    public function test_export_as_markdown_produces_a_readable_report_with_every_section(): void
    {
        $service = $this->makeReportService();
        $report = $service->generate();

        $markdown = $service->export($report, ExportFormat::Markdown);

        self::assertStringContainsString('# Oxy AI Readiness Report', $markdown);
        self::assertStringContainsString('## Validation Results', $markdown);
        self::assertStringContainsString('FAIL', $markdown);
        self::assertStringContainsString('## Recommendations', $markdown);
        self::assertStringContainsString('Fix robots-txt', $markdown);
        self::assertStringContainsString('## Monitoring Events', $markdown);
        self::assertStringContainsString('None.', $markdown);
    }
}
