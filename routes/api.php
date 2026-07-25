<?php

/**
 * REST API routes (`/wp-json/oxy-ai/v1/...`).
 *
 * @package OxyAI
 */

declare(strict_types=1);

use OxyAI\Core\Application;
use OxyAI\Http\Controllers\DiscoveryController;
use OxyAI\Http\Controllers\ValidationController;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\ValidationService;

/**
 * Per docs/29-Developer-Guide.md's "Adding A REST Route" convention:
 * a versioned route file returning a closure that calls
 * register_rest_route(). Deviates from that doc's literal example in
 * one way: the callback/permission_callback arrays reference an
 * *instantiated* controller (`[$controller, 'method']`), not a bare
 * class-string (`[ExampleController::class, 'method']`) — the latter
 * only works for static methods, which would rule out constructor
 * injection (both controllers below need constructor-injected
 * services), the pattern this same guide's Dependency Injection
 * section otherwise requires.
 *
 * Discovery: only GET routes, per that engine's read-only exit
 * criterion — no POST /discovery/scan or /discovery/reset route yet
 * (see DECISIONS.md). Validation: `run` is a POST since running a
 * validator is a genuine action (not a data-mutating one — it doesn't
 * write to WordPress, the filesystem, or the database).
 */
return static function (Application $app): void {
    $discoveryController = new DiscoveryController($app->make(DiscoveryService::class));

    register_rest_route('oxy-ai/v1', '/discovery', [
        'methods' => 'GET',
        'callback' => [$discoveryController, 'index'],
        'permission_callback' => [$discoveryController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/discovery/map', [
        'methods' => 'GET',
        'callback' => [$discoveryController, 'map'],
        'permission_callback' => [$discoveryController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/discovery/resources', [
        'methods' => 'GET',
        'callback' => [$discoveryController, 'resources'],
        'permission_callback' => [$discoveryController, 'authorize'],
    ]);

    $validationController = new ValidationController(
        $app->make(DiscoveryService::class),
        $app->make(ValidationService::class)
    );

    register_rest_route('oxy-ai/v1', '/validation', [
        'methods' => 'GET',
        'callback' => [$validationController, 'index'],
        'permission_callback' => [$validationController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/validation/run', [
        'methods' => 'POST',
        'callback' => [$validationController, 'run'],
        'permission_callback' => [$validationController, 'authorize'],
        'args' => [
            'resource_id' => [
                'required' => true,
                'type' => 'string',
            ],
        ],
    ]);
};
