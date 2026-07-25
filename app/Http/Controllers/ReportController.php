<?php

/**
 * REST controller for the Reporting Engine.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Http\Controllers;

use OxyAI\DTO\ExportFormat;
use OxyAI\DTO\ScanType;
use OxyAI\Services\ReportService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Gated behind `manage_options`, same interim default as every other
 * controller so far. `/reports/history` (multiple past reports),
 * `/reports/templates` (report-type templates), `/reports/share`
 * (email/webhook/expiring links), and `DELETE /reports/cache` (docs/21's
 * own REST list) are not implemented — history/templates need persisted
 * storage this project doesn't have, and share/cache need external-
 * delivery and caching infrastructure that don't exist yet either. See
 * DECISIONS.md.
 */
final class ReportController
{
    public function __construct(private readonly ReportService $reports)
    {
    }

    public function authorize(): bool
    {
        return current_user_can('manage_options');
    }

    public function index(WP_REST_Request $request): WP_REST_Response
    {
        $report = $this->reports->lastReport();

        return new WP_REST_Response(['success' => true, 'data' => $report?->toArray()], 200);
    }

    public function generate(WP_REST_Request $request): WP_REST_Response
    {
        $typeParam = (string) ($request->get_param('type') ?: 'quick');
        $type = ScanType::tryFrom($typeParam);

        if ($type === null) {
            return new WP_REST_Response(
                ['success' => false, 'message' => sprintf('Unknown scan type "%s".', $typeParam)],
                400
            );
        }

        $report = $this->reports->generate($type);

        return new WP_REST_Response(['success' => true, 'data' => $report->toArray()], 200);
    }

    public function export(WP_REST_Request $request): WP_REST_Response
    {
        $formatParam = (string) ($request->get_param('format') ?: 'json');
        $format = ExportFormat::tryFrom($formatParam);

        if ($format === null) {
            return new WP_REST_Response(
                ['success' => false, 'message' => sprintf('Unknown export format "%s".', $formatParam)],
                400
            );
        }

        $report = $this->reports->lastReport() ?? $this->reports->generate();
        $content = $this->reports->export($report, $format);

        return new WP_REST_Response(
            ['success' => true, 'data' => ['format' => $format->value, 'content' => $content]],
            200
        );
    }
}
