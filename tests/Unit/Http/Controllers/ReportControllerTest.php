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
use OxyAI\Http\Controllers\ReportController;
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
use WP_REST_Request;

final class ReportControllerTest extends TestCase
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
            new ValidationResult('robots-txt', 'robots', ValidationStatus::Pass, 'ok', 0.1)
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

    public function test_authorize_checks_the_manage_options_capability(): void
    {
        Functions\expect('current_user_can')->once()->with('manage_options')->andReturn(true);

        self::assertTrue((new ReportController($this->makeReportService()))->authorize());
    }

    public function test_index_returns_null_data_before_any_report_has_been_generated(): void
    {
        $response = (new ReportController($this->makeReportService()))->index(new WP_REST_Request());

        self::assertSame(200, $response->get_status());
        self::assertNull($response->get_data()['data']);
    }

    public function test_generate_rejects_an_unknown_scan_type(): void
    {
        $request = new WP_REST_Request();
        $request->set_param('type', 'not-a-real-type');

        $response = (new ReportController($this->makeReportService()))->generate($request);

        self::assertSame(400, $response->get_status());
    }

    public function test_generate_produces_a_structured_report(): void
    {
        $controller = new ReportController($this->makeReportService());

        $response = $controller->generate(new WP_REST_Request());

        self::assertSame(200, $response->get_status());
        $data = $response->get_data()['data'];
        self::assertSame('quick', $data['audit']['scan_type']);
        self::assertArrayHasKey('recommendations', $data);
        self::assertArrayHasKey('monitoring_events', $data);
    }

    public function test_index_reflects_the_last_report_after_generate(): void
    {
        $controller = new ReportController($this->makeReportService());

        $controller->generate(new WP_REST_Request());
        $response = $controller->index(new WP_REST_Request());

        self::assertNotNull($response->get_data()['data']);
    }

    public function test_export_rejects_an_unknown_format(): void
    {
        $request = new WP_REST_Request();
        $request->set_param('format', 'pdf');

        $response = (new ReportController($this->makeReportService()))->export($request);

        self::assertSame(400, $response->get_status());
    }

    public function test_export_defaults_to_json_and_generates_a_report_if_none_exists_yet(): void
    {
        $response = (new ReportController($this->makeReportService()))->export(new WP_REST_Request());

        self::assertSame(200, $response->get_status());
        self::assertSame('json', $response->get_data()['data']['format']);
        self::assertIsString($response->get_data()['data']['content']);
        self::assertNotSame('', $response->get_data()['data']['content']);
    }

    public function test_export_as_markdown_reuses_the_last_generated_report(): void
    {
        $controller = new ReportController($this->makeReportService());
        $controller->generate(new WP_REST_Request());

        $request = new WP_REST_Request();
        $request->set_param('format', 'markdown');

        $response = $controller->export($request);

        self::assertSame('markdown', $response->get_data()['data']['format']);
        self::assertStringContainsString('# Oxy AI Readiness Report', $response->get_data()['data']['content']);
    }
}
