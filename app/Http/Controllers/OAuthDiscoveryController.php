<?php

/**
 * REST controller for the OAuth Discovery module's combined overview.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Http\Controllers;

use OxyAI\Services\GenerationService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * The per-file preview/save/validate/reset routes live on
 * `OAuthDiscoveryFileController` (one instance per well-known
 * document); this controller only serves the combined `GET
 * /oauth-discovery` overview across all three.
 */
final class OAuthDiscoveryController
{
    private const GENERATOR_IDS = ['openid-configuration', 'oauth-authorization-server', 'oauth-protected-resource'];

    public function __construct(private readonly GenerationService $generation)
    {
    }

    public function authorize(): bool
    {
        return current_user_can('manage_options');
    }

    public function index(WP_REST_Request $request): WP_REST_Response
    {
        $data = [];

        foreach (self::GENERATOR_IDS as $id) {
            $data[$id] = [
                'published' => $this->generation->currentContent($id) !== null,
                'version' => $this->generation->version($id),
            ];
        }

        return new WP_REST_Response(['success' => true, 'data' => $data], 200);
    }
}
