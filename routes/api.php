<?php

/**
 * REST API routes (`/wp-json/oxy-ai/v1/...`).
 *
 * @package OxyAI
 */

declare(strict_types=1);

use OxyAI\Core\Application;
use OxyAI\Http\Controllers\DiscoveryController;
use OxyAI\Services\DiscoveryService;

/**
 * Per docs/29-Developer-Guide.md's "Adding A REST Route" convention:
 * a versioned route file returning a closure that calls
 * register_rest_route(). Deviates from that doc's literal example in
 * one way: the callback/permission_callback arrays reference an
 * *instantiated* controller (`[$controller, 'method']`), not a bare
 * class-string (`[ExampleController::class, 'method']`) — the latter
 * only works for static methods, which would rule out constructor
 * injection (DiscoveryController needs DiscoveryService), the pattern
 * this same guide's Dependency Injection section otherwise requires.
 *
 * Only GET routes this phase, per the Discovery Engine's read-only
 * exit criterion — no POST /discovery/scan or /discovery/reset route
 * yet (see DECISIONS.md).
 */
return static function (Application $app): void {
    $controller = new DiscoveryController($app->make(DiscoveryService::class));

    register_rest_route('oxy-ai/v1', '/discovery', [
        'methods' => 'GET',
        'callback' => [$controller, 'index'],
        'permission_callback' => [$controller, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/discovery/map', [
        'methods' => 'GET',
        'callback' => [$controller, 'map'],
        'permission_callback' => [$controller, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/discovery/resources', [
        'methods' => 'GET',
        'callback' => [$controller, 'resources'],
        'permission_callback' => [$controller, 'authorize'],
    ]);
};
