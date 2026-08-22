<?php

/**
 * REST controller for legacy updater module actions and alpha.5 update/license settings.
 *
 * @package OxyAI
 */
declare(strict_types=1);

namespace OxyAI\Http\Controllers;

use OxyAI\DTO\ValidationResult;
use OxyAI\Exceptions\GenerationException;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\LicenseService;
use OxyAI\Services\UpdaterService;
use OxyAI\Services\ValidationService;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

final class UpdaterController
{
    private const GENERATOR_ID = 'updater';
    private const RESOURCE_ID = 'updater-status';

    public function __construct(
        private readonly DiscoveryService $discovery,
        private readonly ValidationService $validation,
        private readonly GenerationService $generation,
        private readonly ?UpdaterService $updater = null,
        private readonly ?LicenseService $license = null
    ) {
    }

    public function authorize(): bool
    {
        return current_user_can('manage_options');
    }

    /** Legacy endpoint retained for backward compatibility. */
    public function index(WP_REST_Request $request): WP_REST_Response
    {
        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'published' => $this->generation->currentContent(self::GENERATOR_ID) !== null,
                'version' => $this->generation->version(self::GENERATOR_ID),
            ],
        ], 200);
    }

    public function preview(WP_REST_Request $request): WP_REST_Response
    {
        return new WP_REST_Response(['success' => true, 'data' => ['content' => $this->generation->preview(self::GENERATOR_ID)]], 200);
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
            return new WP_REST_Response(['success' => false, 'message' => 'Updater status has not been discovered.'], 404);
        }

        $results = $this->validation->validate($map[self::RESOURCE_ID]);

        return new WP_REST_Response([
            'success' => true,
            'data' => array_map(static fn (ValidationResult $result): array => $result->toArray(), $results),
        ], 200);
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

    public function status(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        if (!$this->updater instanceof UpdaterService) {
            return $this->serviceUnavailable();
        }

        return new WP_REST_Response(['success' => true, 'data' => $this->updater->status()], 200);
    }

    public function check(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        if (!$this->updater instanceof UpdaterService) {
            return $this->serviceUnavailable();
        }

        $manifest = $this->updater->manifest(true);
        if ($manifest instanceof WP_Error) {
            return $manifest;
        }

        return new WP_REST_Response(['success' => true, 'data' => $this->updater->status()], 200);
    }

    public function activateLicense(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        if (!$this->license instanceof LicenseService || !$this->updater instanceof UpdaterService) {
            return $this->serviceUnavailable();
        }

        $key = $request->get_param('key');
        if (!is_string($key)) {
            return new WP_Error('oxy_ai_license_key', __('A license key is required.', 'oxy-ai-readiness'), ['status' => 400]);
        }

        $state = $this->license->activate($key);
        if ($state instanceof WP_Error) {
            return $state;
        }

        return new WP_REST_Response(['success' => true, 'data' => $this->updater->status()], 200);
    }

    public function deleteLicense(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        if (!$this->license instanceof LicenseService || !$this->updater instanceof UpdaterService) {
            return $this->serviceUnavailable();
        }

        $this->license->clear();

        return new WP_REST_Response(['success' => true, 'data' => $this->updater->status()], 200);
    }

    public function autoUpdate(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        if (!$this->updater instanceof UpdaterService) {
            return $this->serviceUnavailable();
        }

        $enabled = $request->get_param('enabled');
        if (!is_bool($enabled)) {
            return new WP_Error('oxy_ai_auto_update', __('The enabled field must be boolean.', 'oxy-ai-readiness'), ['status' => 400]);
        }

        $this->updater->setAutoUpdate($enabled);

        return new WP_REST_Response(['success' => true, 'data' => $this->updater->status()], 200);
    }

    private function serviceUnavailable(): WP_Error
    {
        return new WP_Error('oxy_ai_updater_unavailable', __('The updater service is unavailable.', 'oxy-ai-readiness'), ['status' => 503]);
    }
}
