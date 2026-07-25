<?php

/**
 * The kind of audit scan being run.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\DTO;

/**
 * Per docs/06-Audit-Engine.md's "Scan Types" section. With only
 * Discovery/Validation/Scoring built so far (no Headers/Performance/
 * Security-specific scanners exist yet — those need modules from later
 * phases), Deep/Developer don't yet examine anything Full doesn't;
 * they exist as real, selectable values now so `AuditService`'s API
 * doesn't need to change shape once those scanners exist. Quick is the
 * one genuinely different mode: it reuses whatever Discovery has
 * already found instead of forcing a fresh scan.
 */
enum ScanType: string
{
    case Quick = 'quick';
    case Full = 'full';
    case Deep = 'deep';
    case Developer = 'developer';
}
