<?php

/**
 * REST controller for the Audit Engine.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Http\Controllers;

use OxyAI\DTO\ScanType;
use OxyAI\Services\AuditService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Gated behind `manage_options`, same interim default as every other
 * controller so far. `/audit/fix` and `/audit/verify` (docs/06's own
 * REST list) are not implemented — those belong to the AutoFix Engine,
 * a later phase.
 */
final class AuditController
{
    public function __construct(private readonly AuditService $audit)
    {
    }

    public function authorize(): bool
    {
        return current_user_can('manage_options');
    }

    public function index(WP_REST_Request $request): WP_REST_Response
    {
        $report = $this->audit->lastReport();

        return new WP_REST_Response(['success' => true, 'data' => $report?->toArray()], 200);
    }

    public function start(WP_REST_Request $request): WP_REST_Response
    {
        $typeParam = (string) ($request->get_param('type') ?: 'quick');
        $type = ScanType::tryFrom($typeParam);

        if ($type === null) {
            return new WP_REST_Response(
                ['success' => false, 'message' => sprintf('Unknown scan type "%s".', $typeParam)],
                400
            );
        }

        $report = $this->audit->scan($type);

        return new WP_REST_Response(['success' => true, 'data' => $report->toArray()], 200);
    }
}
