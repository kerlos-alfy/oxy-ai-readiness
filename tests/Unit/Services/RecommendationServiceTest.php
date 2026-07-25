<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Services;

use Brain\Monkey\Actions;
use Mockery;
use OxyAI\Contracts\GeneratorInterface;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;
use OxyAI\Repositories\FileRepository;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\RecommendationService;
use OxyAI\Services\ValidationService;
use OxyAI\Tests\Unit\Support\InMemoryFilesystem;
use OxyAI\Tests\Unit\TestCase;

final class RecommendationServiceTest extends TestCase
{
    private function makeService(bool $autoFixAvailable): RecommendationService
    {
        $generation = new GenerationService(
            new ValidationService(),
            new DiscoveryService(),
            new FileRepository('/base', new InMemoryFilesystem())
        );

        if ($autoFixAvailable) {
            $generator = Mockery::mock(GeneratorInterface::class);
            $generation->registerGenerator('validator-a', $generator);
        }

        return new RecommendationService($generation);
    }

    public function test_generate_produces_a_recommendation_for_each_fail_and_warning(): void
    {
        Actions\expectDone('oxy_ai_recommendations_generated')->once();

        $service = $this->makeService(true);

        $recommendations = $service->generate([
            new ValidationResult('a', 'validator-a', ValidationStatus::Fail, 'broken', 0.1),
            new ValidationResult('b', 'validator-b', ValidationStatus::Warning, 'iffy', 0.1),
            new ValidationResult('c', 'validator-c', ValidationStatus::Pass, 'fine', 0.1),
        ]);

        self::assertCount(2, $recommendations);
        self::assertSame('critical', $recommendations[0]->priority);
        self::assertSame('medium', $recommendations[1]->priority);
    }

    public function test_generate_ignores_pass_info_skipped_and_unknown_results(): void
    {
        $service = $this->makeService(true);

        $recommendations = $service->generate([
            new ValidationResult('a', 'v', ValidationStatus::Pass, '', 0.1),
            new ValidationResult('b', 'v', ValidationStatus::Info, '', 0.1),
            new ValidationResult('c', 'v', ValidationStatus::Skipped, '', 0.1),
            new ValidationResult('d', 'v', ValidationStatus::Unknown, '', 0.1),
        ]);

        self::assertSame([], $recommendations);
    }

    public function test_auto_fix_available_reflects_whether_a_generator_is_registered(): void
    {
        $service = $this->makeService(false);

        $recommendations = $service->generate([
            new ValidationResult('a', 'validator-a', ValidationStatus::Fail, 'broken', 0.1),
        ]);

        self::assertFalse($recommendations[0]->autoFixAvailable);
    }
}
