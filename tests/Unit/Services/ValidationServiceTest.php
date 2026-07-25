<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Services;

use Brain\Monkey\Actions;
use Mockery;
use OxyAI\Contracts\ValidatorInterface;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;
use OxyAI\Exceptions\ModuleException;
use OxyAI\Services\ValidationService;
use OxyAI\Tests\Unit\TestCase;

final class ValidationServiceTest extends TestCase
{
    private function makeResource(): DiscoveredResource
    {
        return new DiscoveredResource(
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
    }

    private function makeValidator(ValidationStatus $status): ValidatorInterface&Mockery\MockInterface
    {
        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->allows('validate')->andReturn(
            new ValidationResult('probe-fixture', 'probe', $status, 'message', 0.1)
        );

        return $validator;
    }

    public function test_validate_runs_every_registered_validator_and_fires_started_and_completed_events(): void
    {
        Actions\expectDone('oxy_ai_validation_started')->once();
        Actions\expectDone('oxy_ai_validation_completed')->once();

        $service = new ValidationService();
        $service->registerValidator('probe', $this->makeValidator(ValidationStatus::Pass));

        $results = $service->validate($this->makeResource());

        self::assertCount(1, $results);
        self::assertSame(ValidationStatus::Pass, $results[0]->status);
    }

    public function test_validate_fires_the_matching_status_event(): void
    {
        Actions\expectDone('oxy_ai_validation_failed')->once();

        $service = new ValidationService();
        $service->registerValidator('probe', $this->makeValidator(ValidationStatus::Fail));

        $service->validate($this->makeResource());

        $this->expectNotToPerformAssertions();
    }

    public function test_register_validator_rejects_a_duplicate_id(): void
    {
        $service = new ValidationService();
        $service->registerValidator('probe', $this->makeValidator(ValidationStatus::Pass));

        $this->expectException(ModuleException::class);

        $service->registerValidator('probe', $this->makeValidator(ValidationStatus::Pass));
    }

    public function test_has_and_count_reflect_registered_validators(): void
    {
        $service = new ValidationService();

        self::assertFalse($service->has('probe'));
        self::assertSame(0, $service->count());

        $service->registerValidator('probe', $this->makeValidator(ValidationStatus::Pass));

        self::assertTrue($service->has('probe'));
        self::assertSame(1, $service->count());
    }
}
