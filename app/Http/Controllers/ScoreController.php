<?php

/**
 * REST controller for the Scoring Engine.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Http\Controllers;

use OxyAI\Services\DiscoveryService;
use OxyAI\Services\ScoringService;
use OxyAI\Services\ValidationService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Chains the three engines built so far exactly per docs/15's own
 * pipeline (Discovery → Validation → ... → Final Score): validates
 * every currently-discovered resource, then scores the combined set of
 * results. Read-only, per docs/15-Scoring-Engine.md's own Security
 * section ("Read Only"). Gated behind `manage_options`, same interim
 * default as every other controller so far.
 */
final class ScoreController
{
    public function __construct(
        private readonly DiscoveryService $discovery,
        private readonly ValidationService $validation,
        private readonly ScoringService $scoring
    ) {
    }

    public function authorize(): bool
    {
        return current_user_can('manage_options');
    }

    public function index(WP_REST_Request $request): WP_REST_Response
    {
        $results = [];

        foreach ($this->discovery->map() as $resource) {
            foreach ($this->validation->validate($resource) as $result) {
                $results[] = $result;
            }
        }

        $score = $this->scoring->calculate($results);

        return new WP_REST_Response(['success' => true, 'data' => $score->toArray()], 200);
    }
}
