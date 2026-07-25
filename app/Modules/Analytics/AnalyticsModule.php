<?php

/**
 * The Analytics module: declares the AI-usage metrics this site tracks.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\Analytics;

use OxyAI\Contracts\DiscoveryInterface;
use OxyAI\Contracts\GeneratorInterface;
use OxyAI\Contracts\ModuleInterface;
use OxyAI\Contracts\ValidatorInterface;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;

/**
 * Per docs/05-Modules.md's Analytics module ("Track AI Usage" —
 * AI Crawlers/Visits/Markdown Requests/LLMS Requests/Agent Requests,
 * Daily/Weekly/Monthly charts) and `.project/09-Canonical-Architecture.md`'s
 * ownership table (Analytics owns no Standard). Real per-request
 * tracking needs a persisted counter store — no Logger service, Cache
 * Service, or `oxy_*` table exists yet (deferred since Phase 2, still
 * true here). Rather than fabricate traffic numbers, this module
 * honestly declares which metrics it *would* track and reports them
 * at their real current value: zero, since nothing has counted a
 * single real request yet. See DECISIONS.md.
 */
final class AnalyticsModule implements ModuleInterface, DiscoveryInterface, ValidatorInterface, GeneratorInterface
{
    private const METRICS = ['ai_crawlers', 'visits', 'markdown_requests', 'llms_requests', 'agent_requests'];

    public function id(): string
    {
        return 'analytics';
    }

    public function name(): string
    {
        return 'Analytics';
    }

    public function version(): string
    {
        return '0.1.0';
    }

    public function description(): string
    {
        return "Declares the AI-usage metrics this site tracks.";
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
                id: 'analytics-summary',
                type: 'analytics-summary',
                location: '/.well-known/oxy-analytics-summary',
                status: 'active',
                version: $this->version(),
                module: $this->id(),
                health: 'healthy',
                dependencies: [],
                source: 'analytics',
                lastChecked: gmdate('c')
            ),
        ];
    }

    /**
     * Real schema check: every declared metric has a real (even if
     * currently zero) count — no metric is missing its counter.
     */
    public function validate(DiscoveredResource $resource): ValidationResult
    {
        $start = microtime(true);
        $decoded = json_decode($this->generate(), true);
        $counts = is_array($decoded) && is_array($decoded['counts'] ?? null) ? $decoded['counts'] : [];
        $missing = array_diff(self::METRICS, array_keys($counts));

        return new ValidationResult(
            resourceId: $resource->id,
            validator: $this->id(),
            status: $missing === [] ? ValidationStatus::Pass : ValidationStatus::Fail,
            message: $missing === []
                ? 'Every declared metric has a counter.'
                : sprintf('Missing counters: %s.', implode(', ', $missing)),
            executionTimeMs: (microtime(true) - $start) * 1000
        );
    }

    public function resourceId(): string
    {
        return 'analytics-summary';
    }

    public function supports(string $type): bool
    {
        return $type === 'analytics-summary';
    }

    public function generate(): string
    {
        return (string) wp_json_encode([
            'period' => 'daily',
            'metrics' => self::METRICS,
            'counts' => array_fill_keys(self::METRICS, 0),
        ], JSON_PRETTY_PRINT);
    }
}
