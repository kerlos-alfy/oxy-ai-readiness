<?php

/**
 * REST controller exposing the Discovery Map.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Http\Controllers;

use OxyAI\Services\DiscoveryService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Read-only per the Phase 4 exit criterion — every method here is a
 * GET handler; nothing mutates state. Gated behind `manage_options`
 * (WordPress's built-in admin capability) as an interim default: the
 * custom capabilities docs/26-Security-Spec.md names (`manage_oxy`,
 * `view_audit`, etc.) aren't registered by any capability system yet
 * (Permissions is part of a Settings/Roles phase that hasn't happened).
 * Response envelope is a minimal, honest subset of
 * docs/24-REST-API-Spec.md's Common Response shape (`success`/`data`) —
 * pagination/request-id/execution-time are not yet meaningful for a
 * small, unpaginated fixture-only result set.
 */
final class DiscoveryController
{
    public function __construct(private readonly DiscoveryService $discovery)
    {
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
                    'resources_count' => count($this->discovery->resources()),
                ],
            ],
            200
        );
    }

    public function map(WP_REST_Request $request): WP_REST_Response
    {
        $map = [];

        foreach ($this->discovery->map() as $id => $resource) {
            $map[$id] = $resource->toArray();
        }

        return new WP_REST_Response(
            [
                'success' => true,
                'data' => $map,
            ],
            200
        );
    }

    public function resources(WP_REST_Request $request): WP_REST_Response
    {
        $resources = array_map(
            static fn ($resource): array => $resource->toArray(),
            $this->discovery->resources()
        );

        return new WP_REST_Response(
            [
                'success' => true,
                'data' => $resources,
            ],
            200
        );
    }
}
