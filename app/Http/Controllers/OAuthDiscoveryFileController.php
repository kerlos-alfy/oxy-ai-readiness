<?php

/**
 * REST controller for one OAuth Discovery well-known document.
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
 * A thin facade over the shared Discovery/Validation/Generation
 * engines, parameterized by which of the three OAuth Discovery
 * documents it serves (`$generatorId`/`$resourceId` are identical for
 * every one of them — see each Generator's own `id()`/`resourceId()`).
 * Instantiated three times in `routes/api.php`, once per document,
 * under `/oauth-discovery/{document}/*` — mirrors every other module's
 * index/preview/save/validate/reset shape exactly, just reused across
 * three documents instead of hand-duplicated three times.
 */
final class OAuthDiscoveryFileController
{
    public function __construct(
        private readonly string $generatorId,
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
                    'published' => $this->generation->currentContent($this->generatorId) !== null,
                    'version' => $this->generation->version($this->generatorId),
                ],
            ],
            200
        );
    }

    public function preview(WP_REST_Request $request): WP_REST_Response
    {
        return new WP_REST_Response(
            ['success' => true, 'data' => ['content' => $this->generation->preview($this->generatorId)]],
            200
        );
    }

    public function save(WP_REST_Request $request): WP_REST_Response
    {
        try {
            $result = $this->generation->publish($this->generatorId);
        } catch (GenerationException $exception) {
            return new WP_REST_Response(['success' => false, 'message' => $exception->getMessage()], 409);
        }

        return new WP_REST_Response(['success' => true, 'data' => $result->toArray()], 200);
    }

    public function validate(WP_REST_Request $request): WP_REST_Response
    {
        $map = $this->discovery->map();

        if (!isset($map[$this->generatorId])) {
            return new WP_REST_Response(
                ['success' => false, 'message' => sprintf('"%s" has not been discovered.', $this->generatorId)],
                404
            );
        }

        $results = $this->validation->validate($map[$this->generatorId]);

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
            $this->generation->rollback($this->generatorId);
        } catch (GenerationException $exception) {
            return new WP_REST_Response(['success' => false, 'message' => $exception->getMessage()], 409);
        }

        return new WP_REST_Response(['success' => true], 200);
    }
}
