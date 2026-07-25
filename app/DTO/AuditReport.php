<?php

/**
 * A structured report from one audit scan.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\DTO;

/**
 * Per docs/06-Audit-Engine.md's Reporting section, narrowed to what
 * this phase's engines can actually populate (Summary, AI Score,
 * Timeline/duration) — Health Score, Charts, and Recommendations
 * belong to engines/phases that don't exist yet.
 */
final class AuditReport
{
    /**
     * @param array<int, ValidationResult> $results
     * @param array<string, int> $summary
     */
    public function __construct(
        public readonly ScanType $scanType,
        public readonly array $results,
        public readonly array $summary,
        public readonly ScoreResult $score,
        public readonly float $durationMs,
        public readonly string $startedAt
    ) {
    }

    /**
     * @return array{
     *     scan_type: string,
     *     summary: array<string, int>,
     *     score: array{
     *         score: float, grade: string, label: string,
     *         confidence: string, trend: string, calculated_at: string
     *     },
     *     duration_ms: float,
     *     started_at: string,
     *     results: array<int, array{
     *         resource_id: string, validator: string, status: string,
     *         message: string, execution_time_ms: float
     *     }>
     * }
     */
    public function toArray(): array
    {
        return [
            'scan_type' => $this->scanType->value,
            'summary' => $this->summary,
            'score' => $this->score->toArray(),
            'duration_ms' => $this->durationMs,
            'started_at' => $this->startedAt,
            'results' => array_map(
                static fn (ValidationResult $result): array => $result->toArray(),
                $this->results
            ),
        ];
    }
}
