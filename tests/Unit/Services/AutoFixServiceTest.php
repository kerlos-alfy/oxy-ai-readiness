<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Services;

use Brain\Monkey\Actions;
use Mockery;
use OxyAI\Contracts\DiscoveryInterface;
use OxyAI\Contracts\GeneratorInterface;
use OxyAI\Contracts\ValidatorInterface;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\DTO\FixTier;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;
use OxyAI\Repositories\FileRepository;
use OxyAI\Services\AutoFixService;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\ValidationService;
use OxyAI\Tests\Unit\Support\InMemoryFilesystem;
use OxyAI\Tests\Unit\TestCase;
use WP_Filesystem_Base;

/**
 * Per docs/28-Testing-Strategy.md's AUTOFIX TESTING and ROLLBACK
 * TESTING sections. Covers what this project's current infrastructure
 * can genuinely exercise: Backup Creation, Execution, Validation,
 * Verification, Success Report, and rollback after Validation Failure
 * and Filesystem Failure specifically. Not covered — no real
 * infrastructure exists yet to test against: Database Failure (no DB),
 * Timeout/Interrupted Request (no async/network layer), Permission
 * Change/Dependency Conflict (no capability-registration or
 * module-dependency system yet), Partial Batch Execution (no batch-fix
 * feature yet). See DECISIONS.md.
 */
final class AutoFixServiceTest extends TestCase
{
    private function makeResource(): DiscoveredResource
    {
        return new DiscoveredResource(
            id: 'fixture-resource',
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
    }

    /**
     * @param ValidationStatus ...$statuses Sequential results returned
     *        across successive validate() calls (publish()'s own
     *        pre-write check consumes one, and AutoFixService's
     *        explicit post-write verify() step consumes another).
     * @return array{0: DiscoveryService, 1: ValidationService, 2: GenerationService, 3: AutoFixService}
     */
    private function makeServices(ValidationStatus ...$statuses): array
    {
        $discoveryProvider = Mockery::mock(DiscoveryInterface::class);
        $discoveryProvider->allows('discover')->andReturn([$this->makeResource()]);
        $discovery = new DiscoveryService();
        $discovery->registerProvider('fixture', $discoveryProvider);

        $results = array_map(
            static fn (ValidationStatus $status): ValidationResult => new ValidationResult(
                'fixture-resource',
                'fixture',
                $status,
                'message',
                0.1
            ),
            $statuses
        );

        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->allows('validate')->andReturn(...$results);
        $validation = new ValidationService();
        $validation->registerValidator('fixture', $validator);

        $files = new FileRepository('/base', new InMemoryFilesystem());
        $generation = new GenerationService($validation, $discovery, $files);

        $autofix = new AutoFixService($generation, $validation, $discovery);

        return [$discovery, $validation, $generation, $autofix];
    }

    private function registerGenerator(GenerationService $generation, string ...$content): void
    {
        $generator = Mockery::mock(GeneratorInterface::class);
        $generator->allows('id')->andReturn('fixture');
        $generator->allows('resourceId')->andReturn('fixture-resource');
        $generator->allows('generate')->andReturn(...$content);

        $generation->registerGenerator('fixture', $generator);
    }

    public function test_has_and_last_result_reflect_registration_and_history(): void
    {
        [, , $generation, $autofix] = $this->makeServices(ValidationStatus::Pass, ValidationStatus::Pass);

        self::assertFalse($autofix->has('fixture'));
        self::assertNull($autofix->lastResult());

        $this->registerGenerator($generation, 'content');
        self::assertTrue($autofix->has('fixture'));

        $result = $autofix->fix('fixture');

        self::assertSame($result, $autofix->lastResult());
    }

    public function test_fix_backs_up_executes_and_verifies_successfully(): void
    {
        Actions\expectDone('oxy_ai_autofix_completed')->once();

        [, , $generation, $autofix] = $this->makeServices(ValidationStatus::Pass, ValidationStatus::Pass);
        $this->registerGenerator($generation, 'fixed content');

        $result = $autofix->fix('fixture');

        self::assertTrue($result->success);
        self::assertFalse($result->pending);
        self::assertSame(1, $result->version);
        self::assertSame('fixed content', $generation->currentContent('fixture'));
    }

    public function test_fix_creates_a_real_backup_that_rollback_can_restore(): void
    {
        [, , $generation, $autofix] = $this->makeServices(
            ValidationStatus::Pass,
            ValidationStatus::Pass,
            ValidationStatus::Pass,
            ValidationStatus::Pass
        );
        $this->registerGenerator($generation, 'version one', 'version two');

        $autofix->fix('fixture');
        $autofix->fix('fixture');

        self::assertSame('version two', $generation->currentContent('fixture'));

        $autofix->rollback('fixture');

        self::assertSame('version one', $generation->currentContent('fixture'));
    }

    public function test_fix_rolls_back_when_verification_fails_after_a_successful_write(): void
    {
        Actions\expectDone('oxy_ai_autofix_rolled_back')->once();

        // Sequence: (1) initial manual publish's pre-check, (2) fix()'s
        // own publish pre-check, (3) fix()'s post-write verification.
        [, , $generation, $autofix] = $this->makeServices(
            ValidationStatus::Pass,
            ValidationStatus::Pass,
            ValidationStatus::Fail
        );
        $this->registerGenerator($generation, 'version one', 'version two');

        $generation->publish('fixture');
        self::assertSame('version one', $generation->currentContent('fixture'));

        $result = $autofix->fix('fixture');

        self::assertFalse($result->success);
        self::assertFalse($result->pending);
        self::assertSame(
            'version one',
            $generation->currentContent('fixture'),
            'Rollback should restore the prior version.'
        );
    }

    public function test_fix_reports_failure_without_writing_when_validation_fails_before_execution(): void
    {
        [, , $generation, $autofix] = $this->makeServices(ValidationStatus::Fail);
        $this->registerGenerator($generation, 'should never be written');

        $result = $autofix->fix('fixture');

        self::assertFalse($result->success);
        self::assertNull($generation->currentContent('fixture'));
    }

    public function test_fix_reports_failure_on_filesystem_write_error_without_corrupting_existing_content(): void
    {
        $discoveryProvider = Mockery::mock(DiscoveryInterface::class);
        $discoveryProvider->allows('discover')->andReturn([$this->makeResource()]);
        $discovery = new DiscoveryService();
        $discovery->registerProvider('fixture', $discoveryProvider);

        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->allows('validate')->andReturn(
            new ValidationResult('fixture-resource', 'fixture', ValidationStatus::Pass, 'ok', 0.1)
        );
        $validation = new ValidationService();
        $validation->registerValidator('fixture', $validator);

        $failingFs = new class extends WP_Filesystem_Base {
            public function exists(string $file): bool
            {
                return false;
            }

            public function is_readable(string $file): bool
            {
                return false;
            }

            public function is_writable(string $file): bool
            {
                return true;
            }

            public function is_dir(string $path): bool
            {
                return true;
            }

            public function get_contents(string $file)
            {
                return false;
            }

            public function put_contents(string $file, string $contents, $mode = false): bool
            {
                return false;
            }

            public function delete(string $file, bool $recursive = false, ?string $type = null): bool
            {
                return true;
            }

            public function mkdir(string $path, $chmod = false, $chown = false, $chgrp = false): bool
            {
                return true;
            }

            public function move(string $source, string $destination, bool $overwrite = false): bool
            {
                return false;
            }
        };

        $generation = new GenerationService($validation, $discovery, new FileRepository('/base', $failingFs));
        $autofix = new AutoFixService($generation, $validation, $discovery);
        $this->registerGenerator($generation, 'unwritable content');

        $result = $autofix->fix('fixture');

        self::assertFalse($result->success);
        self::assertNull($generation->currentContent('fixture'));
    }

    public function test_confirmation_tier_does_not_execute_without_explicit_confirmation(): void
    {
        [, , $generation, $autofix] = $this->makeServices(ValidationStatus::Pass, ValidationStatus::Pass);
        $this->registerGenerator($generation, 'should not be written yet');

        $result = $autofix->fix('fixture', FixTier::Confirmation);

        self::assertTrue($result->pending);
        self::assertFalse($result->success);
        self::assertNull($generation->currentContent('fixture'));
    }

    public function test_confirmation_tier_executes_once_confirmed(): void
    {
        [, , $generation, $autofix] = $this->makeServices(ValidationStatus::Pass, ValidationStatus::Pass);
        $this->registerGenerator($generation, 'confirmed content');

        $result = $autofix->fix('fixture', FixTier::Confirmation, confirmed: true);

        self::assertTrue($result->success);
        self::assertSame('confirmed content', $generation->currentContent('fixture'));
    }

    public function test_developer_tier_also_requires_explicit_confirmation(): void
    {
        [, , $generation, $autofix] = $this->makeServices();
        $this->registerGenerator($generation, 'irrelevant');

        $result = $autofix->fix('fixture', FixTier::Developer);

        self::assertTrue($result->pending);
    }

    public function test_rollback_delegates_to_generation_service_and_fires_its_own_event(): void
    {
        Actions\expectDone('oxy_ai_autofix_rollback_completed')->once();

        [, , $generation, $autofix] = $this->makeServices(
            ValidationStatus::Pass,
            ValidationStatus::Pass,
            ValidationStatus::Pass,
            ValidationStatus::Pass
        );
        $this->registerGenerator($generation, 'version one', 'version two');

        $autofix->fix('fixture');
        $autofix->fix('fixture');
        $autofix->rollback('fixture');

        self::assertSame('version one', $generation->currentContent('fixture'));
    }
}
