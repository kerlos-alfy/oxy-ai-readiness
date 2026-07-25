<?php

/**
 * REST controller running validators against Discovery Map entries.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Http\Controllers;

use OxyAI\DTO\ValidationResult;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\ValidationService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Gated behind `manage_options`, same interim default as
 * `DiscoveryController` (see its docblock and DECISIONS.md). `run()`
 * validates input per docs/26-Security-Spec.md's Input Validation
 * ("Reject Unknown Fields"/require the fields an action needs):
 * `resource_id` must be present and must name a resource the Discovery
 * Engine has actually discovered, otherwise this returns 400/404
 * rather than a confusing empty result.
 */
final class ValidationController
{
    public function __construct(
        private readonly DiscoveryService $discovery,
        private readonly ValidationService $validation
    ) {
    }

    public function authorize(): bool
    {
        return current_user_can('manage_options');
    }

    public function index(WP_REST_Request $request): WP_REST_Response
    {
        return new WP_REST_Response(
            [
                'success' => true,
                'data' => [
                    'validators_count' => $this->validation->count(),
                ],
            ],
            200
        );
    }

    public function run(WP_REST_Request $request): WP_REST_Response
    {
        $resourceId = (string) $request->get_param('resource_id');

        if ($resourceId === '') {
            return new WP_REST_Response(
                ['success' => false, 'message' => 'Missing required parameter: resource_id.'],
                400
            );
        }

        $map = $this->discovery->map();

        if (!isset($map[$resourceId])) {
            return new WP_REST_Response(
                ['success' => false, 'message' => sprintf('Unknown resource "%s".', $resourceId)],
                404
            );
        }

        $results = $this->validation->validate($map[$resourceId]);

        return new WP_REST_Response(
            [
                'success' => true,
                'data' => array_map(
                    static fn (ValidationResult $result): array => $result->toArray(),
                    $results
                ),
            ],
            200
        );
    }
}
