<?php

/**
 * Central Audit Engine: ties Discovery, Validation, and Scoring together.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Services;

use OxyAI\DTO\AuditReport;
use OxyAI\DTO\ScanType;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;

/**
 * Per docs/06-Audit-Engine.md's Scan Flow (Load Modules → Run Scanners
 * → Collect Results → Calculate Score → Generate Report): runs every
 * currently-registered Discovery provider, validates each discovered
 * resource, scores the combined results, and packages the outcome into
 * a structured `AuditReport` — exactly the Phase 9 exit criterion
 * ("Full/Quick scan... returns a structured report").
 *
 * A `Quick` scan reuses whatever `DiscoveryService` has already
 * cached; `Full`/`Deep`/`Developer` force a fresh discovery pass first
 * (`DiscoveryService::reset()`), matching docs' own distinction that a
 * Quick scan is meant to be the fast, cheap option.
 */
final class AuditService
{
    private ?AuditReport $lastReport = null;

    public function __construct(
        private readonly DiscoveryService $discovery,
        private readonly ValidationService $validation,
        private readonly ScoringService $scoring
    ) {
    }

    public function scan(ScanType $type): AuditReport
    {
        $start = microtime(true);
        $startedAt = gmdate('c');

        do_action('oxy_ai_audit_started', $type);

        if ($type !== ScanType::Quick) {
            $this->discovery->reset();
        }

        $results = [];

        foreach ($this->discovery->map() as $resource) {
            foreach ($this->validation->validate($resource) as $result) {
                $results[] = $result;
            }
        }

        $summary = $this->summarize($results);
        $score = $this->scoring->calculate($results);
        $durationMs = (microtime(true) - $start) * 1000;

        $report = new AuditReport($type, $results, $summary, $score, $durationMs, $startedAt);
        $this->lastReport = $report;

        do_action('oxy_ai_audit_completed', $report);

        return $report;
    }

    public function lastReport(): ?AuditReport
    {
        return $this->lastReport;
    }

    /**
     * @param array<int, ValidationResult> $results
     * @return array<string, int>
     */
    private function summarize(array $results): array
    {
        $summary = [];

        foreach (ValidationStatus::cases() as $status) {
            $summary[$status->value] = 0;
        }

        foreach ($results as $result) {
            $summary[$result->status->value]++;
        }

        return $summary;
    }
}
