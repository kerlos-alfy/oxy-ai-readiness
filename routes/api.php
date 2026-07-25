<?php

/**
 * REST API routes (`/wp-json/oxy-ai/v1/...`).
 *
 * @package OxyAI
 */

declare(strict_types=1);

use OxyAI\Core\Application;
use OxyAI\Http\Controllers\AuditController;
use OxyAI\Http\Controllers\DiscoveryController;
use OxyAI\Http\Controllers\GenerationController;
use OxyAI\Http\Controllers\RobotsController;
use OxyAI\Http\Controllers\ScoreController;
use OxyAI\Http\Controllers\ValidationController;
use OxyAI\Services\AuditService;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\ScoringService;
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
 * write to WordPress, the filesystem, or the database). Generation:
 * `/generation/rollback` is not in docs/17-Generation-Engine.md's own
 * REST list (which has `/generation/reset` instead) but the Phase 6
 * exit criterion explicitly requires rollback capability, so it's
 * exposed under its own, more accurate name rather than left
 * unreachable via REST — see DECISIONS.md. Robots: a thin facade over
 * the shared engines (`RobotsController` doesn't reimplement
 * generation/validation logic); `/robots/reset` maps to
 * `GenerationService::rollback()`, not the fuller version-history
 * restore docs/07-Robots-Spec.md describes — that needs persisted
 * settings/version storage this project hasn't built yet. Audit:
 * `/audit/fix` and `/audit/verify` (docs/06's own REST list) are not
 * implemented — those belong to the AutoFix Engine, a later phase.
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

    $generationController = new GenerationController($app->make(GenerationService::class));

    register_rest_route('oxy-ai/v1', '/generation', [
        'methods' => 'GET',
        'callback' => [$generationController, 'index'],
        'permission_callback' => [$generationController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/generation/preview', [
        'methods' => 'GET',
        'callback' => [$generationController, 'preview'],
        'permission_callback' => [$generationController, 'authorize'],
        'args' => [
            'generator_id' => [
                'required' => true,
                'type' => 'string',
            ],
        ],
    ]);

    register_rest_route('oxy-ai/v1', '/generation/publish', [
        'methods' => 'POST',
        'callback' => [$generationController, 'publish'],
        'permission_callback' => [$generationController, 'authorize'],
        'args' => [
            'generator_id' => [
                'required' => true,
                'type' => 'string',
            ],
        ],
    ]);

    register_rest_route('oxy-ai/v1', '/generation/rollback', [
        'methods' => 'POST',
        'callback' => [$generationController, 'rollback'],
        'permission_callback' => [$generationController, 'authorize'],
        'args' => [
            'generator_id' => [
                'required' => true,
                'type' => 'string',
            ],
        ],
    ]);

    $scoreController = new ScoreController(
        $app->make(DiscoveryService::class),
        $app->make(ValidationService::class),
        $app->make(ScoringService::class)
    );

    register_rest_route('oxy-ai/v1', '/score', [
        'methods' => 'GET',
        'callback' => [$scoreController, 'index'],
        'permission_callback' => [$scoreController, 'authorize'],
    ]);

    $robotsController = new RobotsController(
        $app->make(DiscoveryService::class),
        $app->make(ValidationService::class),
        $app->make(GenerationService::class)
    );

    register_rest_route('oxy-ai/v1', '/robots', [
        'methods' => 'GET',
        'callback' => [$robotsController, 'index'],
        'permission_callback' => [$robotsController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/robots/preview', [
        'methods' => 'GET',
        'callback' => [$robotsController, 'preview'],
        'permission_callback' => [$robotsController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/robots/save', [
        'methods' => 'POST',
        'callback' => [$robotsController, 'save'],
        'permission_callback' => [$robotsController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/robots/validate', [
        'methods' => 'POST',
        'callback' => [$robotsController, 'validate'],
        'permission_callback' => [$robotsController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/robots/reset', [
        'methods' => 'POST',
        'callback' => [$robotsController, 'reset'],
        'permission_callback' => [$robotsController, 'authorize'],
    ]);

    $auditController = new AuditController($app->make(AuditService::class));

    register_rest_route('oxy-ai/v1', '/audit', [
        'methods' => 'GET',
        'callback' => [$auditController, 'index'],
        'permission_callback' => [$auditController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/audit/start', [
        'methods' => 'POST',
        'callback' => [$auditController, 'start'],
        'permission_callback' => [$auditController, 'authorize'],
        'args' => [
            'type' => [
                'required' => false,
                'type' => 'string',
                'default' => 'quick',
            ],
        ],
    ]);
};
