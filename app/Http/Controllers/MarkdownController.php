<?php

/**
 * REST controller for the Markdown module.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Http\Controllers;

use OxyAI\DTO\ValidationResult;
use OxyAI\Exceptions\GenerationException;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\ValidationService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * A thin, markdown-specific facade over the shared Discovery/
 * Validation/Generation engines, mirroring `RobotsController` exactly
 * under its own `/markdown/*` namespace.
 */
final class MarkdownController
{
    private const GENERATOR_ID = 'markdown';
    private const RESOURCE_ID = 'markdown-negotiation';

    public function __construct(
        private readonly DiscoveryService $discovery,
        private readonly ValidationService $validation,
        private readonly GenerationService $generation
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
                    'published' => $this->generation->currentContent(self::GENERATOR_ID) !== null,
                    'version' => $this->generation->version(self::GENERATOR_ID),
                ],
            ],
            200
        );
    }

    public function preview(WP_REST_Request $request): WP_REST_Response
    {
        return new WP_REST_Response(
            ['success' => true, 'data' => ['content' => $this->generation->preview(self::GENERATOR_ID)]],
            200
        );
    }

    public function save(WP_REST_Request $request): WP_REST_Response
    {
        try {
            $result = $this->generation->publish(self::GENERATOR_ID);
        } catch (GenerationException $exception) {
            return new WP_REST_Response(['success' => false, 'message' => $exception->getMessage()], 409);
        }

        return new WP_REST_Response(['success' => true, 'data' => $result->toArray()], 200);
    }

    public function validate(WP_REST_Request $request): WP_REST_Response
    {
        $map = $this->discovery->map();

        if (!isset($map[self::RESOURCE_ID])) {
            return new WP_REST_Response(
                ['success' => false, 'message' => 'Markdown negotiation has not been discovered.'],
                404
            );
        }

        $results = $this->validation->validate($map[self::RESOURCE_ID]);

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

    public function reset(WP_REST_Request $request): WP_REST_Response
    {
        try {
            $this->generation->rollback(self::GENERATOR_ID);
        } catch (GenerationException $exception) {
            return new WP_REST_Response(['success' => false, 'message' => $exception->getMessage()], 409);
        }

        return new WP_REST_Response(['success' => true], 200);
    }
}
