<?php

/**
 * The outcome of one score calculation.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\DTO;

final class ScoreResult
{
    public function __construct(
        public readonly float $score,
        public readonly Grade $grade,
        public readonly ConfidenceLevel $confidence,
        public readonly Trend $trend,
        public readonly string $calculatedAt
    ) {
    }

    /**
     * @return array{
     *     score: float,
     *     grade: string,
     *     label: string,
     *     confidence: string,
     *     trend: string,
     *     calculated_at: string
     * }
     */
    public function toArray(): array
    {
        return [
            'score' => $this->score,
            'grade' => $this->grade->value,
            'label' => $this->grade->label(),
            'confidence' => $this->confidence->value,
            'trend' => $this->trend->value,
            'calculated_at' => $this->calculatedAt,
        ];
    }
}
