<?php

/**
 * The canonical score-to-grade-to-label scale.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\DTO;

/**
 * Per `.project/adr/ADR-005-scoring-grading.md` (the single canonical
 * scale referenced by both docs/15-Scoring-Engine.md and
 * docs/06-Audit-Engine.md): 98-100 A+, 95-97 A, 90-94 A-, 85-89 B+,
 * 80-84 B, 75-79 B-, 70-74 C+, 60-69 C, 40-59 D, 0-39 F, with labels
 * Excellent/Advanced/Good/Basic/Poor.
 */
enum Grade: string
{
    case APlus = 'A+';
    case A = 'A';
    case AMinus = 'A-';
    case BPlus = 'B+';
    case B = 'B';
    case BMinus = 'B-';
    case CPlus = 'C+';
    case C = 'C';
    case D = 'D';
    case F = 'F';

    public static function fromScore(float $score): self
    {
        return match (true) {
            $score >= 98 => self::APlus,
            $score >= 95 => self::A,
            $score >= 90 => self::AMinus,
            $score >= 85 => self::BPlus,
            $score >= 80 => self::B,
            $score >= 75 => self::BMinus,
            $score >= 70 => self::CPlus,
            $score >= 60 => self::C,
            $score >= 40 => self::D,
            default => self::F,
        };
    }

    public function label(): string
    {
        // phpcs:ignore PHPCompatibility.Variables.ForbiddenThisUseContexts.OutsideObjectContext
        return match ($this) {
            self::APlus, self::A, self::AMinus => 'Excellent',
            self::BPlus, self::B, self::BMinus => 'Advanced',
            self::CPlus, self::C => 'Good',
            self::D => 'Basic',
            self::F => 'Poor',
        };
    }
}
