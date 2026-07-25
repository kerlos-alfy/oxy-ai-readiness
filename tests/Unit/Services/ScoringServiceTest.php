<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Services;

use Brain\Monkey\Actions;
use OxyAI\DTO\ConfidenceLevel;
use OxyAI\DTO\Grade;
use OxyAI\DTO\Trend;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;
use OxyAI\Services\ScoringService;
use OxyAI\Tests\Unit\TestCase;

final class ScoringServiceTest extends TestCase
{
    private function makeResult(ValidationStatus $status): ValidationResult
    {
        return new ValidationResult('resource', 'validator', $status, 'message', 0.1);
    }

    public function test_calculate_scores_all_passing_results_as_one_hundred(): void
    {
        Actions\expectDone('oxy_ai_score_calculated')->once();
        Actions\expectDone('oxy_ai_trend_updated')->once();
        Actions\expectDone('oxy_ai_confidence_updated')->once();

        $service = new ScoringService();
        $result = $service->calculate([
            $this->makeResult(ValidationStatus::Pass),
            $this->makeResult(ValidationStatus::Pass),
        ]);

        self::assertSame(100.0, $result->score);
        self::assertSame(Grade::APlus, $result->grade);
        self::assertSame(ConfidenceLevel::VeryHigh, $result->confidence);
        self::assertSame(Trend::Unknown, $result->trend);
    }

    public function test_calculate_scores_all_failing_results_as_zero(): void
    {
        $service = new ScoringService();
        $result = $service->calculate([
            $this->makeResult(ValidationStatus::Fail),
            $this->makeResult(ValidationStatus::Fail),
        ]);

        self::assertSame(0.0, $result->score);
        self::assertSame(Grade::F, $result->grade);
    }

    public function test_calculate_gives_warnings_half_credit(): void
    {
        $service = new ScoringService();
        $result = $service->calculate([
            $this->makeResult(ValidationStatus::Pass),
            $this->makeResult(ValidationStatus::Warning),
            $this->makeResult(ValidationStatus::Fail),
        ]);

        // (1.0 + 0.5 + 0.0) / 3 * 100
        self::assertSame(50.0, $result->score);
    }

    public function test_calculate_excludes_info_skipped_and_unknown_from_the_score_but_not_confidence(): void
    {
        $service = new ScoringService();
        $result = $service->calculate([
            $this->makeResult(ValidationStatus::Pass),
            $this->makeResult(ValidationStatus::Info),
            $this->makeResult(ValidationStatus::Skipped),
        ]);

        self::assertSame(100.0, $result->score);
        // 1 applicable out of 3 total = 0.33 ratio -> Low confidence
        self::assertSame(ConfidenceLevel::Low, $result->confidence);
    }

    public function test_calculate_returns_zero_score_and_low_confidence_for_an_empty_result_set(): void
    {
        $service = new ScoringService();
        $result = $service->calculate([]);

        self::assertSame(0.0, $result->score);
        self::assertSame(ConfidenceLevel::Low, $result->confidence);
    }

    public function test_calculate_reports_unknown_trend_on_the_first_call_then_improving_and_declining(): void
    {
        $service = new ScoringService();

        $first = $service->calculate([$this->makeResult(ValidationStatus::Fail)]);
        self::assertSame(Trend::Unknown, $first->trend);

        $second = $service->calculate([$this->makeResult(ValidationStatus::Pass)]);
        self::assertSame(Trend::Improving, $second->trend);

        $third = $service->calculate([$this->makeResult(ValidationStatus::Fail)]);
        self::assertSame(Trend::Declining, $third->trend);

        $fourth = $service->calculate([$this->makeResult(ValidationStatus::Fail)]);
        self::assertSame(Trend::Stable, $fourth->trend);
    }

    public function test_calculate_fires_grade_changed_only_when_the_grade_actually_changes(): void
    {
        Actions\expectDone('oxy_ai_grade_changed')->once();

        $service = new ScoringService();
        $service->calculate([$this->makeResult(ValidationStatus::Fail)]);
        $service->calculate([$this->makeResult(ValidationStatus::Fail)]);
        $service->calculate([$this->makeResult(ValidationStatus::Pass)]);

        $this->expectNotToPerformAssertions();
    }
}
