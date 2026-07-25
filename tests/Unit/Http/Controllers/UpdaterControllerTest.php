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
use OxyAI\Http\Controllers\UpdaterController;
use OxyAI\Repositories\FileRepository;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\ValidationService;
use OxyAI\Tests\Unit\Support\InMemoryFilesystem;
use OxyAI\Tests\Unit\TestCase;
use WP_REST_Request;

final class UpdaterControllerTest extends TestCase
{
    private function makeController(): UpdaterController
    {
        $resource = new DiscoveredResource(
            id: 'updater-status',
            type: 'updater-status',
            location: '/.well-known/oxy-updater-status',
            status: 'active',
            version: '0.1.0',
            module: 'updater',
            health: 'healthy',
            dependencies: [],
            source: 'updater',
            lastChecked: '2026-07-26T00:00:00+00:00'
        );

        $discoveryProvider = Mockery::mock(DiscoveryInterface::class);
        $discoveryProvider->allows('discover')->andReturn([$resource]);
        $discovery = new DiscoveryService();
        $discovery->registerProvider('updater', $discoveryProvider);

        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->allows('validate')->andReturn(
            new ValidationResult('updater-status', 'updater', ValidationStatus::Pass, 'ok', 0.1)
        );
        $validation = new ValidationService();
        $validation->registerValidator('updater', $validator);

        $files = new FileRepository('/base', new InMemoryFilesystem());
        $generation = new GenerationService($validation, $discovery, $files);

        $generator = Mockery::mock(GeneratorInterface::class);
        $generator->allows('resourceId')->andReturn('updater-status');
        $generator->allows('generate')->andReturn('{"current_version":"0.1.0"}');
        $generation->registerGenerator('updater', $generator);

        return new UpdaterController($discovery, $validation, $generation);
    }

    public function test_authorize_checks_the_manage_options_capability(): void
    {
        Functions\expect('current_user_can')->once()->with('manage_options')->andReturn(true);

        self::assertTrue($this->makeController()->authorize());
    }

    public function test_index_reports_not_published_before_save(): void
    {
        $response = $this->makeController()->index(new WP_REST_Request());

        self::assertSame(200, $response->get_status());
        self::assertFalse($response->get_data()['data']['published']);
        self::assertSame(0, $response->get_data()['data']['version']);
    }

    public function test_preview_returns_generated_content_without_publishing(): void
    {
        $controller = $this->makeController();

        $response = $controller->preview(new WP_REST_Request());

        self::assertSame(200, $response->get_status());
        self::assertNotSame('', $response->get_data()['data']['content']);
        self::assertFalse($controller->index(new WP_REST_Request())->get_data()['data']['published']);
    }

    public function test_save_publishes_and_index_then_reports_published(): void
    {
        $controller = $this->makeController();

        $saveResponse = $controller->save(new WP_REST_Request());
        self::assertSame(200, $saveResponse->get_status());
        self::assertSame(1, $saveResponse->get_data()['data']['version']);

        $indexResponse = $controller->index(new WP_REST_Request());
        self::assertTrue($indexResponse->get_data()['data']['published']);
    }

    public function test_validate_returns_validation_results_for_the_discovered_resource(): void
    {
        $response = $this->makeController()->validate(new WP_REST_Request());

        self::assertSame(200, $response->get_status());
        self::assertSame('pass', $response->get_data()['data'][0]['status']);
    }

    public function test_reset_returns_409_when_there_is_nothing_to_roll_back(): void
    {
        $response = $this->makeController()->reset(new WP_REST_Request());

        self::assertSame(409, $response->get_status());
    }

    public function test_reset_rolls_back_after_two_saves(): void
    {
        $controller = $this->makeController();

        $controller->save(new WP_REST_Request());
        $controller->save(new WP_REST_Request());

        $response = $controller->reset(new WP_REST_Request());

        self::assertSame(200, $response->get_status());
        self::assertTrue($response->get_data()['success']);
    }
}
