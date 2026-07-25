<?php

/**
 * Central Scoring Engine: weighted score, grade, confidence, trend.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Services;

use OxyAI\DTO\ConfidenceLevel;
use OxyAI\DTO\Grade;
use OxyAI\DTO\ScoreResult;
use OxyAI\DTO\Trend;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;

/**
 * Per docs/15-Scoring-Engine.md's Scoring Pipeline (Discovery →
 * Validation → Rule Results → Weighting → Category Scores → Confidence
 * → Final Score → Grade). `calculate()` takes an explicit, fixed array
 * of `ValidationResult` (the Phase 5 exit criterion's own phrasing:
 * "a fixed set of validation results") — it is a pure function of that
 * input for score/grade/confidence; only `trend` depends on this
 * instance's own calculation history, which is inherently comparative
 * by definition (docs' "TREND ENGINE": "Track score history").
 *
 * Weighting is simplified to PASS=1.0/WARNING=0.5/FAIL=0.0 rather than
 * docs' Critical/High/Medium/Low/Info severity weights — `ValidationResult`
 * (Phase 5) carries a status, not a severity; true severity-weighted
 * scoring applies once the Audit Engine (a later phase) gives rule
 * results a severity axis. See DECISIONS.md.
 *
 * Trend is in-memory only, like every other engine's registry state so
 * far (no `oxy_*` score-history table exists yet): it only reflects
 * successive calculate() calls within this instance's lifetime, so on
 * a real WordPress request (a fresh `ScoringService` each time) it will
 * read Unknown until a persisted score-history phase exists.
 */
final class ScoringService
{
    private ?float $previousScore = null;

    /**
     * @param array<int, ValidationResult> $results
     */
    public function calculate(array $results): ScoreResult
    {
        $applicable = array_values(array_filter(
            $results,
            static fn (ValidationResult $result): bool => in_array(
                $result->status,
                [ValidationStatus::Pass, ValidationStatus::Warning, ValidationStatus::Fail],
                true
            )
        ));

        $score = $this->scoreFrom($applicable);
        $grade = Grade::fromScore($score);
        $confidence = $this->confidenceFrom($results, $applicable);
        $trend = $this->trendFrom($score);
        $previousGrade = $this->previousScore === null ? null : Grade::fromScore($this->previousScore);

        $this->previousScore = $score;

        $result = new ScoreResult($score, $grade, $confidence, $trend, gmdate('c'));

        do_action('oxy_ai_score_calculated', $result);

        if ($previousGrade !== null && $previousGrade !== $grade) {
            do_action('oxy_ai_grade_changed', $previousGrade, $grade);
        }

        do_action('oxy_ai_trend_updated', $trend);
        do_action('oxy_ai_confidence_updated', $confidence);

        return $result;
    }

    /**
     * @param array<int, ValidationResult> $applicable
     */
    private function scoreFrom(array $applicable): float
    {
        if ($applicable === []) {
            return 0.0;
        }

        $sum = 0.0;

        foreach ($applicable as $result) {
            $sum += match ($result->status) {
                ValidationStatus::Pass => 1.0,
                ValidationStatus::Warning => 0.5,
                default => 0.0,
            };
        }

        return round(($sum / count($applicable)) * 100, 2);
    }

    /**
     * @param array<int, ValidationResult> $all
     * @param array<int, ValidationResult> $applicable
     */
    private function confidenceFrom(array $all, array $applicable): ConfidenceLevel
    {
        if ($all === []) {
            return ConfidenceLevel::Low;
        }

        return ConfidenceLevel::fromRatio(count($applicable) / count($all));
    }

    private function trendFrom(float $score): Trend
    {
        if ($this->previousScore === null) {
            return Trend::Unknown;
        }

        return match (true) {
            $score > $this->previousScore + 0.01 => Trend::Improving,
            $score < $this->previousScore - 0.01 => Trend::Declining,
            default => Trend::Stable,
        };
    }
}
