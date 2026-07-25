<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Http\Controllers;

use Brain\Monkey\Functions;
use Mockery;
use OxyAI\Contracts\DiscoveryInterface;
use OxyAI\Contracts\GeneratorInterface;
use OxyAI\Contracts\ValidatorInterface;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;
use OxyAI\Http\Controllers\AutoFixController;
use OxyAI\Repositories\FileRepository;
use OxyAI\Services\AutoFixService;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\ValidationService;
use OxyAI\Tests\Unit\Support\InMemoryFilesystem;
use OxyAI\Tests\Unit\TestCase;
use WP_REST_Request;

final class AutoFixControllerTest extends TestCase
{
    private function makeController(): AutoFixController
    {
        $resource = new DiscoveredResource(
            id: 'fixture',
            type: 'internal-fixture',
            location: 'internal://fixture',
            status: 'active',
            version: '0.1.0',
            module: 'fixture',
            health: 'healthy',
            dependencies: [],
            source: 'fixture',
            lastChecked: '2026-07-25T00:00:00+00:00'
        );

        $discoveryProvider = Mockery::mock(DiscoveryInterface::class);
        $discoveryProvider->allows('discover')->andReturn([$resource]);
        $discovery = new DiscoveryService();
        $discovery->registerProvider('fixture', $discoveryProvider);

        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->allows('validate')->andReturn(
            new ValidationResult('fixture', 'fixture', ValidationStatus::Pass, 'ok', 0.1)
        );
        $validation = new ValidationService();
        $validation->registerValidator('fixture', $validator);

        $files = new FileRepository('/base', new InMemoryFilesystem());
        $generation = new GenerationService($validation, $discovery, $files);

        $generator = Mockery::mock(GeneratorInterface::class);
        $generator->allows('id')->andReturn('fixture');
        $generator->allows('resourceId')->andReturn('fixture');
        $generator->allows('generate')->andReturn('content');
        $generation->registerGenerator('fixture', $generator);

        return new AutoFixController(new AutoFixService($generation, $validation, $discovery));
    }

    public function test_authorize_checks_the_manage_options_capability(): void
    {
        Functions\expect('current_user_can')->once()->with('manage_options')->andReturn(true);

        self::assertTrue($this->makeController()->authorize());
    }

    public function test_index_returns_null_data_before_any_fix_has_run(): void
    {
        $response = $this->makeController()->index(new WP_REST_Request());

        self::assertSame(200, $response->get_status());
        self::assertNull($response->get_data()['data']);
    }

    public function test_run_rejects_a_missing_generator_id(): void
    {
        $response = $this->makeController()->run(new WP_REST_Request());

        self::assertSame(400, $response->get_status());
    }

    public function test_run_rejects_an_unknown_generator(): void
    {
        $request = new WP_REST_Request();
        $request->set_param('generator_id', 'does-not-exist');

        $response = $this->makeController()->run($request);

        self::assertSame(404, $response->get_status());
    }

    public function test_run_rejects_an_unknown_fix_tier(): void
    {
        $request = new WP_REST_Request();
        $request->set_param('generator_id', 'fixture');
        $request->set_param('tier', 'not-a-real-tier');

        $response = $this->makeController()->run($request);

        self::assertSame(400, $response->get_status());
    }

    public function test_run_applies_a_safe_fix_and_index_then_reflects_it(): void
    {
        $controller = $this->makeController();

        $request = new WP_REST_Request();
        $request->set_param('generator_id', 'fixture');

        $runResponse = $controller->run($request);

        self::assertSame(200, $runResponse->get_status());
        self::assertTrue($runResponse->get_data()['success']);

        $indexResponse = $controller->index(new WP_REST_Request());
        self::assertNotNull($indexResponse->get_data()['data']);
    }

    public function test_run_reports_pending_for_confirmation_tier_without_confirmed_flag(): void
    {
        $request = new WP_REST_Request();
        $request->set_param('generator_id', 'fixture');
        $request->set_param('tier', 'confirmation');

        $response = $this->makeController()->run($request);

        self::assertSame(200, $response->get_status());
        self::assertTrue($response->get_data()['data']['pending']);
    }

    public function test_rollback_rejects_a_missing_generator_id(): void
    {
        $response = $this->makeController()->rollback(new WP_REST_Request());

        self::assertSame(400, $response->get_status());
    }

    public function test_rollback_returns_409_when_there_is_nothing_to_roll_back(): void
    {
        $request = new WP_REST_Request();
        $request->set_param('generator_id', 'fixture');

        $response = $this->makeController()->rollback($request);

        self::assertSame(409, $response->get_status());
    }

    public function test_rollback_succeeds_after_two_runs(): void
    {
        $controller = $this->makeController();

        $request = new WP_REST_Request();
        $request->set_param('generator_id', 'fixture');

        $controller->run($request);
        $controller->run($request);

        $response = $controller->rollback($request);

        self::assertSame(200, $response->get_status());
        self::assertTrue($response->get_data()['success']);
    }
}
