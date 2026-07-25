<?php

/**
 * REST API routes (`/wp-json/oxy-ai/v1/...`).
 *
 * @package OxyAI
 */

declare(strict_types=1);

use OxyAI\Core\Application;
use OxyAI\Http\Controllers\AgentSkillsController;
use OxyAI\Http\Controllers\AnalyticsController;
use OxyAI\Http\Controllers\ApiCatalogController;
use OxyAI\Http\Controllers\AuditController;
use OxyAI\Http\Controllers\AutoFixController;
use OxyAI\Http\Controllers\CommerceController;
use OxyAI\Http\Controllers\ContentSignalsController;
use OxyAI\Http\Controllers\DiscoveryController;
use OxyAI\Http\Controllers\GenerationController;
use OxyAI\Http\Controllers\HeadersController;
use OxyAI\Http\Controllers\LicenseController;
use OxyAI\Http\Controllers\LlmsController;
use OxyAI\Http\Controllers\MarkdownController;
use OxyAI\Http\Controllers\McpController;
use OxyAI\Http\Controllers\MonitoringController;
use OxyAI\Http\Controllers\OAuthDiscoveryController;
use OxyAI\Http\Controllers\OAuthDiscoveryFileController;
use OxyAI\Http\Controllers\RecommendationController;
use OxyAI\Http\Controllers\ReportController;
use OxyAI\Http\Controllers\RobotsController;
use OxyAI\Http\Controllers\ScoreController;
use OxyAI\Http\Controllers\UpdaterController;
use OxyAI\Http\Controllers\ValidationController;
use OxyAI\Services\AuditService;
use OxyAI\Services\AutoFixService;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\MonitoringService;
use OxyAI\Services\RecommendationService;
use OxyAI\Services\ReportService;
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
 * implemented — those belong to the AutoFix Engine, built this same
 * phase but exposed under its own `/autofix/*` namespace. AutoFix:
 * `/autofix/batch` and `/autofix/history` (docs/18's own REST list)
 * aren't implemented — batch needs multi-issue orchestration and
 * history needs persisted storage, neither built yet; verification
 * happens automatically inside `run`, not as a separate `/verify` step.
 * LLMS/Headers/Markdown/Content Signals (Phase 11) each mirror the
 * Robots pattern exactly — thin facades over the shared engines, one
 * GET/preview/save/validate/reset set per module. Monitoring:
 * `/monitoring/scan` is not in docs/20's own REST list, added under its
 * own accurate name since no Scheduler exists yet to trigger it
 * automatically (same precedent as `/generation/rollback`);
 * `/monitoring/history` is not implemented (see routes' own controller
 * docblock). Reporting: `/reports/history`, `/reports/templates`,
 * `/reports/share`, `DELETE /reports/cache` (docs/21's own REST list)
 * are not implemented — see `ReportController`'s docblock. MCP/Agent
 * Skills/API Catalog (Phase 14) each mirror the Robots pattern exactly
 * too. OAuth Discovery mirrors the same shape three times over (one
 * `OAuthDiscoveryFileController` instance per well-known document)
 * plus one combined `GET /oauth-discovery` overview — see
 * `OAuthDiscoveryModule`'s own docblock for why this one module owns
 * three documents instead of one. Commerce/Analytics/License/Updater
 * (Phase 15) are the four platform-support modules ADR-001 lists as
 * owning no Standard (same as Headers) — registered from one shared
 * loop since all four share the identical index/preview/save/validate/
 * reset shape and constructor signature.
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

    $llmsController = new LlmsController(
        $app->make(DiscoveryService::class),
        $app->make(ValidationService::class),
        $app->make(GenerationService::class)
    );

    register_rest_route('oxy-ai/v1', '/llms', [
        'methods' => 'GET',
        'callback' => [$llmsController, 'index'],
        'permission_callback' => [$llmsController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/llms/preview', [
        'methods' => 'GET',
        'callback' => [$llmsController, 'preview'],
        'permission_callback' => [$llmsController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/llms/save', [
        'methods' => 'POST',
        'callback' => [$llmsController, 'save'],
        'permission_callback' => [$llmsController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/llms/validate', [
        'methods' => 'POST',
        'callback' => [$llmsController, 'validate'],
        'permission_callback' => [$llmsController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/llms/reset', [
        'methods' => 'POST',
        'callback' => [$llmsController, 'reset'],
        'permission_callback' => [$llmsController, 'authorize'],
    ]);

    $headersController = new HeadersController(
        $app->make(DiscoveryService::class),
        $app->make(ValidationService::class),
        $app->make(GenerationService::class)
    );

    register_rest_route('oxy-ai/v1', '/headers', [
        'methods' => 'GET',
        'callback' => [$headersController, 'index'],
        'permission_callback' => [$headersController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/headers/preview', [
        'methods' => 'GET',
        'callback' => [$headersController, 'preview'],
        'permission_callback' => [$headersController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/headers/save', [
        'methods' => 'POST',
        'callback' => [$headersController, 'save'],
        'permission_callback' => [$headersController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/headers/validate', [
        'methods' => 'POST',
        'callback' => [$headersController, 'validate'],
        'permission_callback' => [$headersController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/headers/reset', [
        'methods' => 'POST',
        'callback' => [$headersController, 'reset'],
        'permission_callback' => [$headersController, 'authorize'],
    ]);

    $markdownController = new MarkdownController(
        $app->make(DiscoveryService::class),
        $app->make(ValidationService::class),
        $app->make(GenerationService::class)
    );

    register_rest_route('oxy-ai/v1', '/markdown', [
        'methods' => 'GET',
        'callback' => [$markdownController, 'index'],
        'permission_callback' => [$markdownController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/markdown/preview', [
        'methods' => 'GET',
        'callback' => [$markdownController, 'preview'],
        'permission_callback' => [$markdownController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/markdown/save', [
        'methods' => 'POST',
        'callback' => [$markdownController, 'save'],
        'permission_callback' => [$markdownController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/markdown/validate', [
        'methods' => 'POST',
        'callback' => [$markdownController, 'validate'],
        'permission_callback' => [$markdownController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/markdown/reset', [
        'methods' => 'POST',
        'callback' => [$markdownController, 'reset'],
        'permission_callback' => [$markdownController, 'authorize'],
    ]);

    $contentSignalsController = new ContentSignalsController(
        $app->make(DiscoveryService::class),
        $app->make(ValidationService::class),
        $app->make(GenerationService::class)
    );

    register_rest_route('oxy-ai/v1', '/content-signals', [
        'methods' => 'GET',
        'callback' => [$contentSignalsController, 'index'],
        'permission_callback' => [$contentSignalsController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/content-signals/preview', [
        'methods' => 'GET',
        'callback' => [$contentSignalsController, 'preview'],
        'permission_callback' => [$contentSignalsController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/content-signals/save', [
        'methods' => 'POST',
        'callback' => [$contentSignalsController, 'save'],
        'permission_callback' => [$contentSignalsController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/content-signals/validate', [
        'methods' => 'POST',
        'callback' => [$contentSignalsController, 'validate'],
        'permission_callback' => [$contentSignalsController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/content-signals/reset', [
        'methods' => 'POST',
        'callback' => [$contentSignalsController, 'reset'],
        'permission_callback' => [$contentSignalsController, 'authorize'],
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

    $recommendationController = new RecommendationController(
        $app->make(DiscoveryService::class),
        $app->make(ValidationService::class),
        $app->make(RecommendationService::class)
    );

    register_rest_route('oxy-ai/v1', '/recommendations', [
        'methods' => 'GET',
        'callback' => [$recommendationController, 'index'],
        'permission_callback' => [$recommendationController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/recommendations/generate', [
        'methods' => 'POST',
        'callback' => [$recommendationController, 'generate'],
        'permission_callback' => [$recommendationController, 'authorize'],
    ]);

    $autoFixController = new AutoFixController($app->make(AutoFixService::class));

    register_rest_route('oxy-ai/v1', '/autofix', [
        'methods' => 'GET',
        'callback' => [$autoFixController, 'index'],
        'permission_callback' => [$autoFixController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/autofix/run', [
        'methods' => 'POST',
        'callback' => [$autoFixController, 'run'],
        'permission_callback' => [$autoFixController, 'authorize'],
        'args' => [
            'generator_id' => [
                'required' => true,
                'type' => 'string',
            ],
            'tier' => [
                'required' => false,
                'type' => 'string',
                'default' => 'safe',
            ],
            'confirmed' => [
                'required' => false,
                'type' => 'boolean',
                'default' => false,
            ],
        ],
    ]);

    register_rest_route('oxy-ai/v1', '/autofix/rollback', [
        'methods' => 'POST',
        'callback' => [$autoFixController, 'rollback'],
        'permission_callback' => [$autoFixController, 'authorize'],
        'args' => [
            'generator_id' => [
                'required' => true,
                'type' => 'string',
            ],
        ],
    ]);

    $monitoringController = new MonitoringController($app->make(MonitoringService::class));

    register_rest_route('oxy-ai/v1', '/monitoring', [
        'methods' => 'GET',
        'callback' => [$monitoringController, 'index'],
        'permission_callback' => [$monitoringController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/monitoring/status', [
        'methods' => 'GET',
        'callback' => [$monitoringController, 'status'],
        'permission_callback' => [$monitoringController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/monitoring/events', [
        'methods' => 'GET',
        'callback' => [$monitoringController, 'events'],
        'permission_callback' => [$monitoringController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/monitoring/start', [
        'methods' => 'POST',
        'callback' => [$monitoringController, 'start'],
        'permission_callback' => [$monitoringController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/monitoring/stop', [
        'methods' => 'POST',
        'callback' => [$monitoringController, 'stop'],
        'permission_callback' => [$monitoringController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/monitoring/reset', [
        'methods' => 'POST',
        'callback' => [$monitoringController, 'reset'],
        'permission_callback' => [$monitoringController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/monitoring/scan', [
        'methods' => 'POST',
        'callback' => [$monitoringController, 'scan'],
        'permission_callback' => [$monitoringController, 'authorize'],
    ]);

    $reportController = new ReportController($app->make(ReportService::class));

    register_rest_route('oxy-ai/v1', '/reports', [
        'methods' => 'GET',
        'callback' => [$reportController, 'index'],
        'permission_callback' => [$reportController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/reports/generate', [
        'methods' => 'POST',
        'callback' => [$reportController, 'generate'],
        'permission_callback' => [$reportController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/reports/export', [
        'methods' => 'POST',
        'callback' => [$reportController, 'export'],
        'permission_callback' => [$reportController, 'authorize'],
    ]);

    $mcpController = new McpController(
        $app->make(DiscoveryService::class),
        $app->make(ValidationService::class),
        $app->make(GenerationService::class)
    );

    register_rest_route('oxy-ai/v1', '/mcp', [
        'methods' => 'GET',
        'callback' => [$mcpController, 'index'],
        'permission_callback' => [$mcpController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/mcp/preview', [
        'methods' => 'GET',
        'callback' => [$mcpController, 'preview'],
        'permission_callback' => [$mcpController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/mcp/save', [
        'methods' => 'POST',
        'callback' => [$mcpController, 'save'],
        'permission_callback' => [$mcpController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/mcp/validate', [
        'methods' => 'POST',
        'callback' => [$mcpController, 'validate'],
        'permission_callback' => [$mcpController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/mcp/reset', [
        'methods' => 'POST',
        'callback' => [$mcpController, 'reset'],
        'permission_callback' => [$mcpController, 'authorize'],
    ]);

    $agentSkillsController = new AgentSkillsController(
        $app->make(DiscoveryService::class),
        $app->make(ValidationService::class),
        $app->make(GenerationService::class)
    );

    register_rest_route('oxy-ai/v1', '/agent-skills', [
        'methods' => 'GET',
        'callback' => [$agentSkillsController, 'index'],
        'permission_callback' => [$agentSkillsController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/agent-skills/preview', [
        'methods' => 'GET',
        'callback' => [$agentSkillsController, 'preview'],
        'permission_callback' => [$agentSkillsController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/agent-skills/save', [
        'methods' => 'POST',
        'callback' => [$agentSkillsController, 'save'],
        'permission_callback' => [$agentSkillsController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/agent-skills/validate', [
        'methods' => 'POST',
        'callback' => [$agentSkillsController, 'validate'],
        'permission_callback' => [$agentSkillsController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/agent-skills/reset', [
        'methods' => 'POST',
        'callback' => [$agentSkillsController, 'reset'],
        'permission_callback' => [$agentSkillsController, 'authorize'],
    ]);

    $apiCatalogController = new ApiCatalogController(
        $app->make(DiscoveryService::class),
        $app->make(ValidationService::class),
        $app->make(GenerationService::class)
    );

    register_rest_route('oxy-ai/v1', '/api-catalog', [
        'methods' => 'GET',
        'callback' => [$apiCatalogController, 'index'],
        'permission_callback' => [$apiCatalogController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/api-catalog/preview', [
        'methods' => 'GET',
        'callback' => [$apiCatalogController, 'preview'],
        'permission_callback' => [$apiCatalogController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/api-catalog/save', [
        'methods' => 'POST',
        'callback' => [$apiCatalogController, 'save'],
        'permission_callback' => [$apiCatalogController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/api-catalog/validate', [
        'methods' => 'POST',
        'callback' => [$apiCatalogController, 'validate'],
        'permission_callback' => [$apiCatalogController, 'authorize'],
    ]);

    register_rest_route('oxy-ai/v1', '/api-catalog/reset', [
        'methods' => 'POST',
        'callback' => [$apiCatalogController, 'reset'],
        'permission_callback' => [$apiCatalogController, 'authorize'],
    ]);

    $oauthDiscoveryController = new OAuthDiscoveryController($app->make(GenerationService::class));

    register_rest_route('oxy-ai/v1', '/oauth-discovery', [
        'methods' => 'GET',
        'callback' => [$oauthDiscoveryController, 'index'],
        'permission_callback' => [$oauthDiscoveryController, 'authorize'],
    ]);

    $oauthDiscoveryDocuments = ['openid-configuration', 'oauth-authorization-server', 'oauth-protected-resource'];

    foreach ($oauthDiscoveryDocuments as $oauthDiscoveryDocument) {
        $oauthDiscoveryFileController = new OAuthDiscoveryFileController(
            $oauthDiscoveryDocument,
            $app->make(DiscoveryService::class),
            $app->make(ValidationService::class),
            $app->make(GenerationService::class)
        );

        register_rest_route('oxy-ai/v1', "/oauth-discovery/{$oauthDiscoveryDocument}", [
            'methods' => 'GET',
            'callback' => [$oauthDiscoveryFileController, 'index'],
            'permission_callback' => [$oauthDiscoveryFileController, 'authorize'],
        ]);

        register_rest_route('oxy-ai/v1', "/oauth-discovery/{$oauthDiscoveryDocument}/preview", [
            'methods' => 'GET',
            'callback' => [$oauthDiscoveryFileController, 'preview'],
            'permission_callback' => [$oauthDiscoveryFileController, 'authorize'],
        ]);

        register_rest_route('oxy-ai/v1', "/oauth-discovery/{$oauthDiscoveryDocument}/save", [
            'methods' => 'POST',
            'callback' => [$oauthDiscoveryFileController, 'save'],
            'permission_callback' => [$oauthDiscoveryFileController, 'authorize'],
        ]);

        register_rest_route('oxy-ai/v1', "/oauth-discovery/{$oauthDiscoveryDocument}/validate", [
            'methods' => 'POST',
            'callback' => [$oauthDiscoveryFileController, 'validate'],
            'permission_callback' => [$oauthDiscoveryFileController, 'authorize'],
        ]);

        register_rest_route('oxy-ai/v1', "/oauth-discovery/{$oauthDiscoveryDocument}/reset", [
            'methods' => 'POST',
            'callback' => [$oauthDiscoveryFileController, 'reset'],
            'permission_callback' => [$oauthDiscoveryFileController, 'authorize'],
        ]);
    }

    $platformControllerClasses = [
        'commerce' => CommerceController::class,
        'analytics' => AnalyticsController::class,
        'license' => LicenseController::class,
        'updater' => UpdaterController::class,
    ];

    foreach ($platformControllerClasses as $platformSlug => $platformControllerClass) {
        $platformController = new $platformControllerClass(
            $app->make(DiscoveryService::class),
            $app->make(ValidationService::class),
            $app->make(GenerationService::class)
        );

        register_rest_route('oxy-ai/v1', "/{$platformSlug}", [
            'methods' => 'GET',
            'callback' => [$platformController, 'index'],
            'permission_callback' => [$platformController, 'authorize'],
        ]);

        register_rest_route('oxy-ai/v1', "/{$platformSlug}/preview", [
            'methods' => 'GET',
            'callback' => [$platformController, 'preview'],
            'permission_callback' => [$platformController, 'authorize'],
        ]);

        register_rest_route('oxy-ai/v1', "/{$platformSlug}/save", [
            'methods' => 'POST',
            'callback' => [$platformController, 'save'],
            'permission_callback' => [$platformController, 'authorize'],
        ]);

        register_rest_route('oxy-ai/v1', "/{$platformSlug}/validate", [
            'methods' => 'POST',
            'callback' => [$platformController, 'validate'],
            'permission_callback' => [$platformController, 'authorize'],
        ]);

        register_rest_route('oxy-ai/v1', "/{$platformSlug}/reset", [
            'methods' => 'POST',
            'callback' => [$platformController, 'reset'],
            'permission_callback' => [$platformController, 'authorize'],
        ]);
    }
};
