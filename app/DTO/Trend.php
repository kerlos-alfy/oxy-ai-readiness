<?php

/**
 * Direction of change between two successive score calculations.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\DTO;

/**
 * Per docs/15-Scoring-Engine.md's "TREND STATUS" list. `Unknown` is the
 * only possible value the very first time a score is ever calculated —
 * there is nothing yet to compare against.
 */
enum Trend: string
{
    case Improving = 'improving';
    case Stable = 'stable';
    case Declining = 'declining';
    case Unknown = 'unknown';
}
