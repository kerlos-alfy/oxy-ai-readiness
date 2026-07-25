<?php

/**
 * How urgently a detected change should be surfaced to an admin.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\DTO;

/**
 * Per docs/20-Monitoring-Engine.md's "NOTIFICATION PRIORITY" scale
 * (Critical, High, Medium, Low, Informational) — kept as the full,
 * real five-value scale rather than a narrower one, matching
 * `ScanType`'s own precedent of including every documented value even
 * though only some are currently distinguishable. `MonitoringService`
 * only ever produces three of them: Critical (a FAIL among the
 * revalidation results, or the resource disappearing entirely),
 * Medium (a WARNING with no FAIL), Informational (otherwise). High and
 * Low have no genuine signal to derive from yet — `ValidationResult`
 * (Phase 5) carries a pass/warn/fail status, not a severity, the same
 * limitation `ScoringService` already documented for its own weighting.
 * See DECISIONS.md.
 */
enum NotificationPriority: string
{
    case Critical = 'critical';
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';
    case Informational = 'informational';
}
