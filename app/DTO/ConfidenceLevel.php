<?php

/**
 * How much of the score calculation's input was actually usable.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\DTO;

/**
 * Per docs/15-Scoring-Engine.md's "CONFIDENCE SCORE" output list
 * (Very High/High/Medium/Low). Derived from the ratio of validation
 * results that were actually PASS/WARNING/FAIL (applicable) versus the
 * total supplied, including INFO/SKIPPED/UNKNOWN ones the score
 * calculation itself excludes. This is a narrower proxy than docs' full
 * factor list (Completed Rules, Skipped Rules, Unavailable Resources,
 * Server Restrictions, Plugin Conflicts, Version Compatibility) — those
 * other factors need an Audit Engine (Phase 9) to exist first; ratio of
 * applicable-to-total is the one factor already meaningful with only
 * the Validation Engine (Phase 5) in place.
 */
enum ConfidenceLevel: string
{
    case VeryHigh = 'very_high';
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';

    public static function fromRatio(float $applicableRatio): self
    {
        return match (true) {
            $applicableRatio >= 0.95 => self::VeryHigh,
            $applicableRatio >= 0.80 => self::High,
            $applicableRatio >= 0.50 => self::Medium,
            default => self::Low,
        };
    }
}
