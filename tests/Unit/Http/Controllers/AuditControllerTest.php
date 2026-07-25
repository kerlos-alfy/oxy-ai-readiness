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
use OxyAI\Http\Controllers\AuditController;
use OxyAI\Services\AuditService;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\ScoringService;
use OxyAI\Services\ValidationService;
use OxyAI\Tests\Unit\TestCase;
use WP_REST_Request;

final class AuditControllerTest extends TestCase
{
    private function makeAuditService(): AuditService
    {
        $resource = new DiscoveredResource(
            id: 'fixture-a',
            type: 'internal-fixture',
            location: 'internal://fixture-a',
            status: 'active',
            version: '0.1.0',
            module: 'fixture',
            health: 'healthy',
            dependencies: [],
            source: 'fixture',
            lastChecked: '2026-07-25T00:00:00+00:00'
        );

        $provider = Mockery::mock(DiscoveryInterface::class);
        $provider->allows('discover')->andReturn([$resource]);
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

    public function test_authorize_checks_the_manage_options_capability(): void
    {
        Functions\expect('current_user_can')->once()->with('manage_options')->andReturn(true);

        self::assertTrue((new AuditController($this->makeAuditService()))->authorize());
    }

    public function test_index_returns_null_data_before_any_scan_has_run(): void
    {
        $response = (new AuditController($this->makeAuditService()))->index(new WP_REST_Request());

        self::assertSame(200, $response->get_status());
        self::assertNull($response->get_data()['data']);
    }

    public function test_start_rejects_an_unknown_scan_type(): void
    {
        $request = new WP_REST_Request();
        $request->set_param('type', 'not-a-real-type');

        $response = (new AuditController($this->makeAuditService()))->start($request);

        self::assertSame(400, $response->get_status());
    }

    public function test_start_runs_a_scan_and_returns_a_structured_report(): void
    {
        $controller = new AuditController($this->makeAuditService());

        $request = new WP_REST_Request();
        $request->set_param('type', 'quick');

        $response = $controller->start($request);

        self::assertSame(200, $response->get_status());
        $data = $response->get_data()['data'];
        self::assertSame('quick', $data['scan_type']);
        self::assertSame(100.0, $data['score']['score']);
        self::assertCount(1, $data['results']);
    }

    public function test_start_defaults_to_quick_when_no_type_is_given(): void
    {
        $controller = new AuditController($this->makeAuditService());

        $response = $controller->start(new WP_REST_Request());

        self::assertSame('quick', $response->get_data()['data']['scan_type']);
    }

    public function test_index_reflects_the_last_scan_after_start(): void
    {
        $controller = new AuditController($this->makeAuditService());

        $controller->start(new WP_REST_Request());
        $response = $controller->index(new WP_REST_Request());

        self::assertNotNull($response->get_data()['data']);
    }
}
