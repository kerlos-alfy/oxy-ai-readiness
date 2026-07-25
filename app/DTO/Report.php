<?php

/**
 * An aggregated snapshot of audit, recommendation, and monitoring data.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\DTO;

/**
 * Per docs/21-Reporting-Engine.md's "DATA SOURCES" list (Audit,
 * Discovery, Scoring, Validation, Monitoring, Recommendation, Auto
 * Fix Engines), narrowed to the three this phase's `ReportService`
 * actually aggregates: an `AuditReport` (itself already Discovery +
 * Validation + Scoring, per Phase 9), `Recommendation`s derived from
 * that same audit's results, and current `MonitoringEvent`s. This is
 * docs' own "Technical Report" shape — Executive/Agency/White
 * Label/Compliance report types need business-summary copywriting,
 * client branding, and compliance-framework mappings this project has
 * no source of; not fabricated here. See DECISIONS.md.
 */
final class Report
{
    /**
     * @param array<int, Recommendation> $recommendations
     * @param array<int, MonitoringEvent> $monitoringEvents
     */
    public function __construct(
        public readonly string $id,
        public readonly string $generatedAt,
        public readonly AuditReport $auditReport,
        public readonly array $recommendations,
        public readonly array $monitoringEvents
    ) {
    }

    /**
     * @return array{
     *     id: string,
     *     generated_at: string,
     *     audit: array<string, mixed>,
     *     recommendations: array<int, array{
     *         id: string, title: string, description: string,
     *         category: string, priority: string, auto_fix_available: bool
     *     }>,
     *     monitoring_events: array<int, array<string, mixed>>
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'generated_at' => $this->generatedAt,
            'audit' => $this->auditReport->toArray(),
            'recommendations' => array_map(
                static fn (Recommendation $recommendation): array => $recommendation->toArray(),
                $this->recommendations
            ),
            'monitoring_events' => array_map(
                static fn (MonitoringEvent $event): array => $event->toArray(),
                $this->monitoringEvents
            ),
        ];
    }
}
