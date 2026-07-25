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
use OxyAI\Http\Controllers\OAuthDiscoveryFileController;
use OxyAI\Repositories\FileRepository;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\ValidationService;
use OxyAI\Tests\Unit\Support\InMemoryFilesystem;
use OxyAI\Tests\Unit\TestCase;
use WP_REST_Request;

/**
 * Exercised here with a plain mocked `GeneratorInterface` under one
 * representative document id (`oauth-protected-resource`) rather than
 * the real `OAuthProtectedResourceGenerator` — this controller only
 * ever forwards `$generatorId` as a lookup key into the shared engines
 * (see its own docblock), so it behaves identically for whichever of
 * the three real documents it's constructed with. The real generators'
 * own content is covered by `OAuthDiscoveryGeneratorsTest`.
 */
final class OAuthDiscoveryFileControllerTest extends TestCase
{
    private function makeController(): OAuthDiscoveryFileController
    {
        $resource = new DiscoveredResource(
            id: 'oauth-protected-resource',
            type: 'oauth-protected-resource',
            location: '/.well-known/oauth-protected-resource',
            status: 'active',
            version: '0.1.0',
            module: 'oauth-discovery',
            health: 'healthy',
            dependencies: [],
            source: 'oauth-discovery',
            lastChecked: '2026-07-26T00:00:00+00:00'
        );

        $discoveryProvider = Mockery::mock(DiscoveryInterface::class);
        $discoveryProvider->allows('discover')->andReturn([$resource]);
        $discovery = new DiscoveryService();
        $discovery->registerProvider('oauth-discovery', $discoveryProvider);

        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->allows('validate')->andReturn(
            new ValidationResult('oauth-protected-resource', 'oauth-discovery', ValidationStatus::Pass, 'ok', 0.1)
        );
        $validation = new ValidationService();
        $validation->registerValidator('oauth-discovery', $validator);

        $files = new FileRepository('/base', new InMemoryFilesystem());
        $generation = new GenerationService($validation, $discovery, $files);

        $generator = Mockery::mock(GeneratorInterface::class);
        $generator->allows('resourceId')->andReturn('oauth-protected-resource');
        $generator->allows('generate')->andReturn('{"resource":"https://example.test/wp-json/oxy-ai/v1"}');
        $generation->registerGenerator('oauth-protected-resource', $generator);

        return new OAuthDiscoveryFileController('oauth-protected-resource', $discovery, $validation, $generation);
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
