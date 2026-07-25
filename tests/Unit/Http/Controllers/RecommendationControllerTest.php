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
use OxyAI\Http\Controllers\RecommendationController;
use OxyAI\Repositories\FileRepository;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\RecommendationService;
use OxyAI\Services\ValidationService;
use OxyAI\Tests\Unit\Support\InMemoryFilesystem;
use OxyAI\Tests\Unit\TestCase;
use WP_REST_Request;

final class RecommendationControllerTest extends TestCase
{
    private function makeController(): RecommendationController
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
            new ValidationResult('fixture', 'fixture', ValidationStatus::Fail, 'broken', 0.1)
        );
        $validation = new ValidationService();
        $validation->registerValidator('fixture', $validator);

        $files = new FileRepository('/base', new InMemoryFilesystem());
        $generation = new GenerationService($validation, $discovery, $files);

        return new RecommendationController($discovery, $validation, new RecommendationService($generation));
    }

    public function test_authorize_checks_the_manage_options_capability(): void
    {
        Functions\expect('current_user_can')->once()->with('manage_options')->andReturn(true);

        self::assertTrue($this->makeController()->authorize());
    }

    public function test_generate_returns_a_recommendation_for_the_failing_fixture(): void
    {
        $response = $this->makeController()->generate(new WP_REST_Request());

        self::assertSame(200, $response->get_status());
        $data = $response->get_data()['data'];
        self::assertCount(1, $data);
        self::assertSame('critical', $data[0]['priority']);
    }

    public function test_index_is_an_alias_for_generate(): void
    {
        $response = $this->makeController()->index(new WP_REST_Request());

        self::assertSame(200, $response->get_status());
        self::assertCount(1, $response->get_data()['data']);
    }
}
