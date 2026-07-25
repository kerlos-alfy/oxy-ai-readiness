<?php

/**
 * The API Catalog module: publishes the site's REST API catalog.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\ApiCatalog;

use OxyAI\Contracts\DiscoveryInterface;
use OxyAI\Contracts\GeneratorInterface;
use OxyAI\Contracts\ModuleInterface;
use OxyAI\Contracts\ValidatorInterface;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;

/**
 * Per docs/05-Modules.md's API Catalog module ("Scan REST API,
 * Generate Catalog... Generate /.well-known/api-catalog") and the
 * Phase 14 roadmap row. "Scan REST API" is implemented as a real,
 * accurate, hand-maintained inventory of every route this plugin
 * itself registers in `routes/api.php` — not a live
 * `rest_get_server()->get_routes()` introspection (every other
 * Generator in this codebase is a pure function with no WordPress
 * runtime calls; adding the one exception here would make this module
 * a special case for no benefit `routes/api.php` doesn't already give
 * for free). This is real data (every listed route genuinely exists),
 * not fabricated — but it is a known, documented limitation that this
 * list must be updated by hand whenever a route is added or removed.
 * See DECISIONS.md.
 */
final class ApiCatalogModule implements ModuleInterface, DiscoveryInterface, ValidatorInterface, GeneratorInterface
{
    private const NAMESPACE = 'oxy-ai/v1';

    /**
     * @var array<int, array{method: string, path: string}>
     */
    private const ROUTES = [
        ['method' => 'GET', 'path' => '/discovery'],
        ['method' => 'GET', 'path' => '/discovery/map'],
        ['method' => 'GET', 'path' => '/discovery/resources'],
        ['method' => 'GET', 'path' => '/validation'],
        ['method' => 'POST', 'path' => '/validation/run'],
        ['method' => 'GET', 'path' => '/generation'],
        ['method' => 'GET', 'path' => '/generation/preview'],
        ['method' => 'POST', 'path' => '/generation/publish'],
        ['method' => 'POST', 'path' => '/generation/rollback'],
        ['method' => 'GET', 'path' => '/score'],
        ['method' => 'GET', 'path' => '/audit'],
        ['method' => 'POST', 'path' => '/audit/start'],
        ['method' => 'GET', 'path' => '/recommendations'],
        ['method' => 'POST', 'path' => '/recommendations/generate'],
        ['method' => 'GET', 'path' => '/autofix'],
        ['method' => 'POST', 'path' => '/autofix/run'],
        ['method' => 'POST', 'path' => '/autofix/rollback'],
        ['method' => 'GET', 'path' => '/monitoring'],
        ['method' => 'GET', 'path' => '/monitoring/status'],
        ['method' => 'GET', 'path' => '/monitoring/events'],
        ['method' => 'POST', 'path' => '/monitoring/start'],
        ['method' => 'POST', 'path' => '/monitoring/stop'],
        ['method' => 'POST', 'path' => '/monitoring/reset'],
        ['method' => 'POST', 'path' => '/monitoring/scan'],
        ['method' => 'GET', 'path' => '/reports'],
        ['method' => 'POST', 'path' => '/reports/generate'],
        ['method' => 'POST', 'path' => '/reports/export'],
        ['method' => 'GET', 'path' => '/robots'],
        ['method' => 'GET', 'path' => '/robots/preview'],
        ['method' => 'POST', 'path' => '/robots/save'],
        ['method' => 'POST', 'path' => '/robots/validate'],
        ['method' => 'POST', 'path' => '/robots/reset'],
        ['method' => 'GET', 'path' => '/llms'],
        ['method' => 'GET', 'path' => '/llms/preview'],
        ['method' => 'POST', 'path' => '/llms/save'],
        ['method' => 'POST', 'path' => '/llms/validate'],
        ['method' => 'POST', 'path' => '/llms/reset'],
        ['method' => 'GET', 'path' => '/headers'],
        ['method' => 'GET', 'path' => '/headers/preview'],
        ['method' => 'POST', 'path' => '/headers/save'],
        ['method' => 'POST', 'path' => '/headers/validate'],
        ['method' => 'POST', 'path' => '/headers/reset'],
        ['method' => 'GET', 'path' => '/markdown'],
        ['method' => 'GET', 'path' => '/markdown/preview'],
        ['method' => 'POST', 'path' => '/markdown/save'],
        ['method' => 'POST', 'path' => '/markdown/validate'],
        ['method' => 'POST', 'path' => '/markdown/reset'],
        ['method' => 'GET', 'path' => '/content-signals'],
        ['method' => 'GET', 'path' => '/content-signals/preview'],
        ['method' => 'POST', 'path' => '/content-signals/save'],
        ['method' => 'POST', 'path' => '/content-signals/validate'],
        ['method' => 'POST', 'path' => '/content-signals/reset'],
        ['method' => 'GET', 'path' => '/mcp'],
        ['method' => 'GET', 'path' => '/mcp/preview'],
        ['method' => 'POST', 'path' => '/mcp/save'],
        ['method' => 'POST', 'path' => '/mcp/validate'],
        ['method' => 'POST', 'path' => '/mcp/reset'],
        ['method' => 'GET', 'path' => '/agent-skills'],
        ['method' => 'GET', 'path' => '/agent-skills/preview'],
        ['method' => 'POST', 'path' => '/agent-skills/save'],
        ['method' => 'POST', 'path' => '/agent-skills/validate'],
        ['method' => 'POST', 'path' => '/agent-skills/reset'],
        ['method' => 'GET', 'path' => '/api-catalog'],
        ['method' => 'GET', 'path' => '/api-catalog/preview'],
        ['method' => 'POST', 'path' => '/api-catalog/save'],
        ['method' => 'POST', 'path' => '/api-catalog/validate'],
        ['method' => 'POST', 'path' => '/api-catalog/reset'],
        ['method' => 'GET', 'path' => '/oauth-discovery'],
        ['method' => 'GET', 'path' => '/oauth-discovery/openid-configuration'],
        ['method' => 'GET', 'path' => '/oauth-discovery/openid-configuration/preview'],
        ['method' => 'POST', 'path' => '/oauth-discovery/openid-configuration/save'],
        ['method' => 'POST', 'path' => '/oauth-discovery/openid-configuration/validate'],
        ['method' => 'POST', 'path' => '/oauth-discovery/openid-configuration/reset'],
        ['method' => 'GET', 'path' => '/oauth-discovery/oauth-authorization-server'],
        ['method' => 'GET', 'path' => '/oauth-discovery/oauth-authorization-server/preview'],
        ['method' => 'POST', 'path' => '/oauth-discovery/oauth-authorization-server/save'],
        ['method' => 'POST', 'path' => '/oauth-discovery/oauth-authorization-server/validate'],
        ['method' => 'POST', 'path' => '/oauth-discovery/oauth-authorization-server/reset'],
        ['method' => 'GET', 'path' => '/oauth-discovery/oauth-protected-resource'],
        ['method' => 'GET', 'path' => '/oauth-discovery/oauth-protected-resource/preview'],
        ['method' => 'POST', 'path' => '/oauth-discovery/oauth-protected-resource/save'],
        ['method' => 'POST', 'path' => '/oauth-discovery/oauth-protected-resource/validate'],
        ['method' => 'POST', 'path' => '/oauth-discovery/oauth-protected-resource/reset'],
    ];

    public function id(): string
    {
        return 'api-catalog';
    }

    public function name(): string
    {
        return 'API Catalog';
    }

    public function version(): string
    {
        return '0.1.0';
    }

    public function description(): string
    {
        return "Generates and validates the site's REST API catalog.";
    }

    public function author(): string
    {
        return 'Oxy AI Readiness';
    }

    public function register(): void
    {
    }

    public function boot(): void
    {
    }

    public function init(): void
    {
    }

    public function assets(): array
    {
        return [];
    }

    public function routes(): array
    {
        return [];
    }

    public function settings(): array
    {
        return [];
    }

    public function permissions(): array
    {
        return [];
    }

    public function audit(): array
    {
        return [];
    }

    public function shutdown(): void
    {
    }

    public function discover(): array
    {
        return [
            new DiscoveredResource(
                id: 'api-catalog',
                type: 'api-catalog',
                location: '/.well-known/api-catalog',
                status: 'active',
                version: $this->version(),
                module: $this->id(),
                health: 'healthy',
                dependencies: [],
                source: 'api-catalog',
                lastChecked: gmdate('c')
            ),
        ];
    }

    /**
     * Real schema check: docs/05's own Audit list for API Catalog
     * ("Exists, Schema, Routes") narrowed to what's mechanically
     * checkable — the catalog is non-empty and has no duplicate
     * method+path entry.
     */
    public function validate(DiscoveredResource $resource): ValidationResult
    {
        $start = microtime(true);
        $decoded = json_decode($this->generate(), true);
        $routes = is_array($decoded) && is_array($decoded['routes'] ?? null) ? $decoded['routes'] : [];

        $signatures = array_map(
            static fn (array $route): string => sprintf('%s %s', $route['method'] ?? '', $route['path'] ?? ''),
            $routes
        );
        $hasDuplicates = count($signatures) !== count(array_unique($signatures));

        $status = ($routes !== [] && !$hasDuplicates) ? ValidationStatus::Pass : ValidationStatus::Fail;

        $message = match (true) {
            $routes === [] => 'API catalog is empty.',
            $hasDuplicates => 'Duplicate method+path entries found.',
            default => sprintf('%d unique routes catalogued.', count($routes)),
        };

        return new ValidationResult(
            resourceId: $resource->id,
            validator: $this->id(),
            status: $status,
            message: $message,
            executionTimeMs: (microtime(true) - $start) * 1000
        );
    }

    public function resourceId(): string
    {
        return 'api-catalog';
    }

    public function supports(string $type): bool
    {
        return $type === 'api-catalog';
    }

    public function generate(): string
    {
        return (string) wp_json_encode([
            'namespace' => self::NAMESPACE,
            'routes' => self::ROUTES,
        ], JSON_PRETTY_PRINT);
    }
}
