<?php

/**
 * The possible outcomes of a single validator run.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\DTO;

/**
 * Per docs/16-Validation-Engine.md's "VALIDATION RESULTS" list. A
 * native backed enum rather than a bare string, so "deterministic" (the
 * Phase 5 exit criterion's own word) is enforced by the type system,
 * not by convention.
 */
enum ValidationStatus: string
{
    case Pass = 'pass';
    case Warning = 'warning';
    case Fail = 'fail';
    case Info = 'info';
    case Skipped = 'skipped';
    case Unknown = 'unknown';
}
