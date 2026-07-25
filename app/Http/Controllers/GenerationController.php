<?php

/**
 * REST controller for the Generation Engine's preview/publish/rollback.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Http\Controllers;

use OxyAI\Exceptions\GenerationException;
use OxyAI\Services\GenerationService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Gated behind `manage_options`, same interim default as
 * `DiscoveryController`/`ValidationController` (see DECISIONS.md).
 * `publish`/`rollback` validate `generator_id` input the same way
 * `ValidationController::run()` validates `resource_id` — required,
 * must name something actually registered — and translate a thrown
 * `GenerationException` (validation-failed, never-discovered,
 * nothing-to-roll-back) into a 409 Conflict rather than a 500, since
 * these are expected, recoverable pipeline states, not server errors.
 */
final class GenerationController
{
    public function __construct(private readonly GenerationService $generation)
    {
    }

    public function authorize(): bool
    {
        return current_user_can('manage_options');
    }

    public function index(WP_REST_Request $request): WP_REST_Response
    {
        return new WP_REST_Response(['success' => true], 200);
    }

    public function preview(WP_REST_Request $request): WP_REST_Response
    {
        $generatorId = (string) $request->get_param('generator_id');

        if ($generatorId === '' || !$this->generation->has($generatorId)) {
            return $this->missingGenerator($generatorId);
        }

        return new WP_REST_Response(
            [
                'success' => true,
                'data' => ['output' => $this->generation->preview($generatorId)],
            ],
            200
        );
    }

    public function publish(WP_REST_Request $request): WP_REST_Response
    {
        $generatorId = (string) $request->get_param('generator_id');

        if ($generatorId === '' || !$this->generation->has($generatorId)) {
            return $this->missingGenerator($generatorId);
        }

        try {
            $result = $this->generation->publish($generatorId);
        } catch (GenerationException $exception) {
            return new WP_REST_Response(['success' => false, 'message' => $exception->getMessage()], 409);
        }

        return new WP_REST_Response(['success' => true, 'data' => $result->toArray()], 200);
    }

    public function rollback(WP_REST_Request $request): WP_REST_Response
    {
        $generatorId = (string) $request->get_param('generator_id');

        if ($generatorId === '' || !$this->generation->has($generatorId)) {
            return $this->missingGenerator($generatorId);
        }

        try {
            $this->generation->rollback($generatorId);
        } catch (GenerationException $exception) {
            return new WP_REST_Response(['success' => false, 'message' => $exception->getMessage()], 409);
        }

        return new WP_REST_Response(['success' => true], 200);
    }

    private function missingGenerator(string $generatorId): WP_REST_Response
    {
        if ($generatorId === '') {
            return new WP_REST_Response(
                ['success' => false, 'message' => 'Missing required parameter: generator_id.'],
                400
            );
        }

        return new WP_REST_Response(
            ['success' => false, 'message' => sprintf('Unknown generator "%s".', $generatorId)],
            404
        );
    }
}
