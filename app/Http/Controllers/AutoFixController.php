<?php

/**
 * REST controller for the Auto Fix Engine.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Http\Controllers;

use OxyAI\DTO\FixTier;
use OxyAI\Exceptions\GenerationException;
use OxyAI\Services\AutoFixService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Gated behind `manage_options`, same interim default as every other
 * controller so far. `/autofix/batch`, `/autofix/history`, and a
 * standalone `/autofix/verify` (docs/18's own REST list) are not
 * implemented — batch needs a multi-issue orchestration model this
 * phase doesn't build, history needs persisted storage, and
 * verification already happens automatically inside `run` (see
 * `AutoFixService::fix()`) rather than as a separate callable step.
 */
final class AutoFixController
{
    public function __construct(private readonly AutoFixService $autofix)
    {
    }

    public function authorize(): bool
    {
        return current_user_can('manage_options');
    }

    public function index(WP_REST_Request $request): WP_REST_Response
    {
        $result = $this->autofix->lastResult();

        return new WP_REST_Response(['success' => true, 'data' => $result?->toArray()], 200);
    }

    public function run(WP_REST_Request $request): WP_REST_Response
    {
        $generatorId = (string) $request->get_param('generator_id');

        if ($generatorId === '') {
            return new WP_REST_Response(
                ['success' => false, 'message' => 'Missing required parameter: generator_id.'],
                400
            );
        }

        if (!$this->autofix->has($generatorId)) {
            return new WP_REST_Response(
                ['success' => false, 'message' => sprintf('Unknown generator "%s".', $generatorId)],
                404
            );
        }

        $tier = FixTier::tryFrom((string) ($request->get_param('tier') ?: 'safe'));

        if ($tier === null) {
            return new WP_REST_Response(['success' => false, 'message' => 'Unknown fix tier.'], 400);
        }

        $confirmed = (bool) $request->get_param('confirmed');

        $result = $this->autofix->fix($generatorId, $tier, $confirmed);

        return new WP_REST_Response(['success' => $result->success, 'data' => $result->toArray()], 200);
    }

    public function rollback(WP_REST_Request $request): WP_REST_Response
    {
        $generatorId = (string) $request->get_param('generator_id');

        if ($generatorId === '') {
            return new WP_REST_Response(
                ['success' => false, 'message' => 'Missing required parameter: generator_id.'],
                400
            );
        }

        if (!$this->autofix->has($generatorId)) {
            return new WP_REST_Response(
                ['success' => false, 'message' => sprintf('Unknown generator "%s".', $generatorId)],
                404
            );
        }

        try {
            $this->autofix->rollback($generatorId);
        } catch (GenerationException $exception) {
            return new WP_REST_Response(['success' => false, 'message' => $exception->getMessage()], 409);
        }

        return new WP_REST_Response(['success' => true], 200);
    }
}
