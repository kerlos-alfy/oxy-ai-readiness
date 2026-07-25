<?php

/**
 * Contract for a registered validator.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Contracts;

use OxyAI\DTO\DiscoveredResource;
use OxyAI\DTO\ValidationResult;

/**
 * Per docs/22-Plugin-SDK.md's SDK Interfaces list. A Module that has
 * something to validate implements this and registers itself with
 * `ValidationService`; the Validation Engine calls validate() with a
 * Discovery Map entry and gets back a deterministic PASS/WARN/FAIL
 * result — the Phase 5 exit criterion, verbatim.
 */
interface ValidatorInterface
{
    public function validate(DiscoveredResource $resource): ValidationResult;
}
