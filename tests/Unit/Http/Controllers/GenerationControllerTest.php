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
use OxyAI\Http\Controllers\GenerationController;
use OxyAI\Repositories\FileRepository;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\ValidationService;
use OxyAI\Tests\Unit\Support\InMemoryFilesystem;
use OxyAI\Tests\Unit\TestCase;
use WP_REST_Request;

final class GenerationControllerTest extends TestCase
{
    private function makeService(ValidationStatus $status = ValidationStatus::Pass): GenerationService
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
            new ValidationResult('probe-fixture', 'probe', $status, 'message', 0.1)
        );
        $validation = new ValidationService();
        $validation->registerValidator('probe', $validator);

        $files = new FileRepository('/base', new InMemoryFilesystem());
        $generation = new GenerationService($validation, $discovery, $files);

        $generator = Mockery::mock(GeneratorInterface::class);
        $generator->allows('id')->andReturn('probe');
        $generator->allows('resourceId')->andReturn('probe-fixture');
        $generator->allows('generate')->andReturn('generated content');

        $generation->registerGenerator('probe', $generator);

        return $generation;
    }

    public function test_authorize_checks_the_manage_options_capability(): void
    {
        Functions\expect('current_user_can')->once()->with('manage_options')->andReturn(true);

        $controller = new GenerationController($this->makeService());

        self::assertTrue($controller->authorize());
    }

    public function test_preview_rejects_a_missing_generator_id(): void
    {
        $controller = new GenerationController($this->makeService());

        $response = $controller->preview(new WP_REST_Request());

        self::assertSame(400, $response->get_status());
    }

    public function test_preview_rejects_an_unknown_generator_id(): void
    {
        $controller = new GenerationController($this->makeService());

        $request = new WP_REST_Request();
        $request->set_param('generator_id', 'does-not-exist');

        $response = $controller->preview($request);

        self::assertSame(404, $response->get_status());
    }

    public function test_preview_returns_generated_output_without_publishing(): void
    {
        $service = $this->makeService();
        $controller = new GenerationController($service);

        $request = new WP_REST_Request();
        $request->set_param('generator_id', 'probe');

        $response = $controller->preview($request);

        self::assertSame(200, $response->get_status());
        self::assertSame('generated content', $response->get_data()['data']['output']);
        self::assertNull($service->currentContent('probe'));
    }

    public function test_publish_writes_and_returns_a_result_when_validation_passes(): void
    {
        $controller = new GenerationController($this->makeService(ValidationStatus::Pass));

        $request = new WP_REST_Request();
        $request->set_param('generator_id', 'probe');

        $response = $controller->publish($request);

        self::assertSame(200, $response->get_status());
        self::assertTrue($response->get_data()['success']);
        self::assertSame(1, $response->get_data()['data']['version']);
    }

    public function test_publish_returns_409_when_validation_fails(): void
    {
        $controller = new GenerationController($this->makeService(ValidationStatus::Fail));

        $request = new WP_REST_Request();
        $request->set_param('generator_id', 'probe');

        $response = $controller->publish($request);

        self::assertSame(409, $response->get_status());
        self::assertFalse($response->get_data()['success']);
    }

    public function test_rollback_returns_409_when_there_is_nothing_to_roll_back(): void
    {
        $controller = new GenerationController($this->makeService());

        $request = new WP_REST_Request();
        $request->set_param('generator_id', 'probe');

        $response = $controller->rollback($request);

        self::assertSame(409, $response->get_status());
    }

    public function test_rollback_restores_the_prior_version_after_two_publishes(): void
    {
        $service = $this->makeService();
        $controller = new GenerationController($service);

        $request = new WP_REST_Request();
        $request->set_param('generator_id', 'probe');

        $controller->publish($request);
        $controller->publish($request);

        $response = $controller->rollback($request);

        self::assertSame(200, $response->get_status());
        self::assertTrue($response->get_data()['success']);
    }
}
