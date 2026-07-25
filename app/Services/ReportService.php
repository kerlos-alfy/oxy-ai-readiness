<?php

/**
 * Central Reporting Engine: aggregates audit, recommendation, and
 * monitoring data into an exportable Report.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Services;

use OxyAI\DTO\ExportFormat;
use OxyAI\DTO\Report;
use OxyAI\DTO\ScanType;

/**
 * Per docs/21-Reporting-Engine.md's pipeline (Data Sources → Normalize
 * → Aggregate → Analyze → Generate Report → Visualize → Export →
 * Share): `generate()` is Normalize/Aggregate/Analyze — it runs a real
 * audit scan (Discovery + Validation + Scoring, already tied together
 * by `AuditService`), derives `Recommendation`s from that scan's
 * results, and folds in whatever `MonitoringService` has observed so
 * far. `export()` is the Export step. Visualize (charts) and Share
 * (email/webhook/Slack/expiring links) need rendering and external-
 * delivery infrastructure this project doesn't have — deferred, see
 * DECISIONS.md.
 */
final class ReportService
{
    private ?Report $lastReport = null;

    public function __construct(
        private readonly AuditService $audit,
        private readonly RecommendationService $recommendation,
        private readonly MonitoringService $monitoring
    ) {
    }

    public function generate(ScanType $type = ScanType::Quick): Report
    {
        do_action('oxy_ai_report_started');

        $auditReport = $this->audit->scan($type);

        $report = new Report(
            id: 'report-' . bin2hex(random_bytes(8)),
            generatedAt: gmdate('c'),
            auditReport: $auditReport,
            recommendations: $this->recommendation->generate($auditReport->results),
            monitoringEvents: $this->monitoring->events()
        );

        $this->lastReport = $report;

        do_action('oxy_ai_report_completed', $report);

        return $report;
    }

    public function lastReport(): ?Report
    {
        return $this->lastReport;
    }

    public function export(Report $report, ExportFormat $format): string
    {
        $content = match ($format) {
            ExportFormat::Json => (string) wp_json_encode($report->toArray(), JSON_PRETTY_PRINT),
            ExportFormat::Markdown => $this->toMarkdown($report),
        };

        do_action('oxy_ai_report_exported', $report, $format);

        return $content;
    }

    private function toMarkdown(Report $report): string
    {
        $score = $report->auditReport->score;

        $lines = [
            sprintf('# Oxy AI Readiness Report — %s', $report->id),
            '',
            sprintf('Generated: %s', $report->generatedAt),
            sprintf('Score: %s (%s — %s)', $score->score, $score->grade->value, $score->grade->label()),
            '',
            '## Validation Results',
            '',
        ];

        foreach ($report->auditReport->results as $result) {
            $lines[] = sprintf(
                '- **%s** `%s` (%s): %s',
                strtoupper($result->status->value),
                $result->resourceId,
                $result->validator,
                $result->message
            );
        }

        $lines[] = '';
        $lines[] = '## Recommendations';
        $lines[] = '';

        if ($report->recommendations === []) {
            $lines[] = 'None.';
        }

        foreach ($report->recommendations as $recommendation) {
            $lines[] = sprintf(
                '- **%s** (%s): %s',
                $recommendation->title,
                $recommendation->priority,
                $recommendation->description
            );
        }

        $lines[] = '';
        $lines[] = '## Monitoring Events';
        $lines[] = '';

        if ($report->monitoringEvents === []) {
            $lines[] = 'None.';
        }

        foreach ($report->monitoringEvents as $event) {
            $lines[] = sprintf(
                '- **%s** `%s` (%s): %s',
                strtoupper($event->changeType->value),
                $event->resourceId,
                $event->priority->value,
                $event->message
            );
        }

        return implode("\n", $lines) . "\n";
    }
}
