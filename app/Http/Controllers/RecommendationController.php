<?php

/**
 * REST controller for the Recommendation Engine.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Http\Controllers;

use OxyAI\DTO\Recommendation;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\RecommendationService;
use OxyAI\Services\ValidationService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Read-only. Gated behind `manage_options`, same interim default as
 * every other controller so far.
 */
final class RecommendationController
{
    public function __construct(
        private readonly DiscoveryService $discovery,
        private readonly ValidationService $validation,
        private readonly RecommendationService $recommendations
    ) {
    }

    public function authorize(): bool
    {
        return current_user_can('manage_options');
    }

    public function index(WP_REST_Request $request): WP_REST_Response
    {
        return $this->generate($request);
    }

    public function generate(WP_REST_Request $request): WP_REST_Response
    {
        $results = [];

        foreach ($this->discovery->map() as $resource) {
            foreach ($this->validation->validate($resource) as $result) {
                $results[] = $result;
            }
        }

        $recommendations = $this->recommendations->generate($results);

        return new WP_REST_Response(
            [
                'success' => true,
                'data' => array_map(
                    static fn (Recommendation $recommendation): array => $recommendation->toArray(),
                    $recommendations
                ),
            ],
            200
        );
    }
}
