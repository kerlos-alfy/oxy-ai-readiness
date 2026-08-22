<?php

/**
 * Alpha.5 license and signed-updater REST routes.
 *
 * @package OxyAI
 */

declare(strict_types=1);

use OxyAI\Core\Application;
use OxyAI\Http\Controllers\UpdaterController;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\LicenseService;
use OxyAI\Services\UpdaterService;
use OxyAI\Services\ValidationService;

return static function (Application $app): void {
    $controller = new UpdaterController(
        $app->make(DiscoveryService::class),
        $app->make(ValidationService::class),
        $app->make(GenerationService::class),
        $app->make(UpdaterService::class),
        $app->make(LicenseService::class)
    );

    register_rest_route('oxy-ai/v1', '/updater/status', [
        'methods' => 'GET',
        'callback' => [$controller, 'status'],
        'permission_callback' => [$controller, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/updater/check', [
        'methods' => 'POST',
        'callback' => [$controller, 'check'],
        'permission_callback' => [$controller, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/updater/license', [
        [
            'methods' => 'POST',
            'callback' => [$controller, 'activateLicense'],
            'permission_callback' => [$controller, 'authorize'],
            'args' => [
                'key' => ['required' => true, 'type' => 'string'],
            ],
        ],
        [
            'methods' => 'DELETE',
            'callback' => [$controller, 'deleteLicense'],
            'permission_callback' => [$controller, 'authorize'],
        ],
    ]);

    register_rest_route('oxy-ai/v1', '/updater/auto-update', [
        'methods' => 'POST',
        'callback' => [$controller, 'autoUpdate'],
        'permission_callback' => [$controller, 'authorize'],
        'args' => [
            'enabled' => ['required' => true, 'type' => 'boolean'],
        ],
    ]);
};
