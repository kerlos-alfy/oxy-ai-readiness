<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Services;

use Brain\Monkey\Actions;
use Mockery;
use OxyAI\Contracts\DiscoveryInterface;
use OxyAI\Contracts\GeneratorInterface;
use OxyAI\Contracts\ValidatorInterface;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;
use OxyAI\Exceptions\GenerationException;
use OxyAI\Exceptions\ModuleException;
use OxyAI\Repositories\FileRepository;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\ValidationService;
use OxyAI\Tests\Unit\Support\InMemoryFilesystem;
use OxyAI\Tests\Unit\TestCase;

final class GenerationServiceTest extends TestCase
{
    private function makeResource(string $status = 'active'): DiscoveredResource
    {
        return new DiscoveredResource(
            id: 'probe-fixture',
            type: 'internal-fixture',
            location: 'internal://probe',
            status: $status,
            version: '0.1.0',
            module: 'probe',
            health: 'healthy',
            dependencies: [],
            source: 'fixture',
            lastChecked: '2026-07-25T00:00:00+00:00'
        );
    }

    private function makeService(DiscoveredResource $resource, ValidationStatus $status): GenerationService
    {
        $discoveryProvider = Mockery::mock(DiscoveryInterface::class);
        $discoveryProvider->allows('discover')->andReturn([$resource]);

        $discovery = new DiscoveryService();
        $discovery->registerProvider('probe', $discoveryProvider);

        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->allows('validate')->andReturn(
            new ValidationResult($resource->id, 'probe', $status, 'message', 0.1)
        );

        $validation = new ValidationService();
        $validation->registerValidator('probe', $validator);

        $files = new FileRepository('/base', new InMemoryFilesystem());

        return new GenerationService($validation, $discovery, $files);
    }

    private function makeGenerator(string ...$content): GeneratorInterface&Mockery\MockInterface
    {
        $generator = Mockery::mock(GeneratorInterface::class);
        $generator->allows('id')->andReturn('probe');
        $generator->allows('resourceId')->andReturn('probe-fixture');
        $generator->allows('generate')->andReturn(...$content);

        return $generator;
    }

    public function test_publish_writes_output_and_returns_a_result_when_validation_passes(): void
    {
        Actions\expectDone('oxy_ai_generation_published')->once();

        $service = $this->makeService($this->makeResource(), ValidationStatus::Pass);
        $service->registerGenerator('probe', $this->makeGenerator('hello'));

        $result = $service->publish('probe');

        self::assertSame(1, $result->version);
        self::assertSame('probe.txt', $result->path);
        self::assertSame(1, $service->version('probe'));
        self::assertSame('hello', $service->currentContent('probe'));
    }

    public function test_publish_refuses_to_write_when_validation_fails(): void
    {
        $service = $this->makeService($this->makeResource('inactive'), ValidationStatus::Fail);
        $service->registerGenerator('probe', $this->makeGenerator('should-not-be-written'));

        $this->expectException(GenerationException::class);

        try {
            $service->publish('probe');
        } finally {
            self::assertNull($service->currentContent('probe'));
        }
    }

    public function test_publish_throws_when_the_resource_has_not_been_discovered(): void
    {
        $service = $this->makeService($this->makeResource(), ValidationStatus::Pass);

        $generator = Mockery::mock(GeneratorInterface::class);
        $generator->allows('id')->andReturn('other');
        $generator->allows('resourceId')->andReturn('unknown-resource');

        $service->registerGenerator('other', $generator);

        $this->expectException(GenerationException::class);

        $service->publish('other');
    }

    public function test_rollback_restores_the_previous_version(): void
    {
        Actions\expectDone('oxy_ai_generation_rolled_back')->once();

        $service = $this->makeService($this->makeResource(), ValidationStatus::Pass);
        $service->registerGenerator('probe', $this->makeGenerator('version-one', 'version-two'));

        $service->publish('probe');
        $service->publish('probe');

        self::assertSame(2, $service->version('probe'));
        self::assertSame('version-two', $service->currentContent('probe'));

        $service->rollback('probe');

        self::assertSame(1, $service->version('probe'));
        self::assertSame('version-one', $service->currentContent('probe'));
    }

    public function test_rollback_throws_when_there_is_no_prior_version(): void
    {
        $service = $this->makeService($this->makeResource(), ValidationStatus::Pass);
        $service->registerGenerator('probe', $this->makeGenerator('only-version'));

        $service->publish('probe');

        $this->expectException(GenerationException::class);

        $service->rollback('probe');
    }

    public function test_register_generator_rejects_a_duplicate_id(): void
    {
        $service = $this->makeService($this->makeResource(), ValidationStatus::Pass);
        $service->registerGenerator('probe', $this->makeGenerator('a'));

        $this->expectException(ModuleException::class);

        $service->registerGenerator('probe', $this->makeGenerator('b'));
    }

    public function test_preview_generates_without_publishing(): void
    {
        $service = $this->makeService($this->makeResource(), ValidationStatus::Pass);
        $service->registerGenerator('probe', $this->makeGenerator('previewed'));

        $output = $service->preview('probe');

        self::assertSame('previewed', $output);
        self::assertSame('previewed', $service->cached('probe'));
        self::assertNull($service->currentContent('probe'));
    }
}
