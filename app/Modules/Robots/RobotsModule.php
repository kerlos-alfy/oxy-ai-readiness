<?php

/**
 * The Robots module: manages robots.txt.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\Robots;

use OxyAI\Contracts\DiscoveryInterface;
use OxyAI\Contracts\GeneratorInterface;
use OxyAI\Contracts\ModuleInterface;
use OxyAI\Contracts\ValidatorInterface;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;

/**
 * The first real, user-facing module (Phase 8's "one concrete,
 * well-specified feature"), proving Discovery→Generation→Validation→
 * Scoring end-to-end the same way `Modules/Probe` proved each engine in
 * isolation. Per docs/07-Robots-Spec.md, scoped to this phase's actual
 * exit criterion — generation + validation + REST — not the document's
 * full aspirational feature set (visual builder, version history with
 * restore, third-party SEO-plugin merge detection, import/export):
 * those need a Settings/DB-persistence phase and the Admin UI phase
 * this project hasn't reached yet. See DECISIONS.md.
 *
 * The rule set is a fixed, hardcoded default — WordPress's own
 * standard virtual-robots.txt disallow (`/wp-admin/`, with
 * `admin-ajax.php` explicitly allowed) plus an "Allow AI" template
 * (docs/07's own AI Templates list) for the documented AI crawlers.
 * Not user-customizable yet: that needs persisted settings, which
 * needs a DB-infra phase this project hasn't reached.
 */
final class RobotsModule implements ModuleInterface, DiscoveryInterface, ValidatorInterface, GeneratorInterface
{
    /** @var array<int, RobotsRule> */
    private readonly array $rules;

    public function __construct()
    {
        $this->rules = [
            new RobotsRule(userAgent: '*', disallow: ['/wp-admin/'], allow: ['/wp-admin/admin-ajax.php']),
            new RobotsRule(userAgent: 'GPTBot', allow: ['/']),
            new RobotsRule(userAgent: 'ChatGPT-User', allow: ['/']),
            new RobotsRule(userAgent: 'Google-Extended', allow: ['/']),
            new RobotsRule(userAgent: 'ClaudeBot', allow: ['/']),
            new RobotsRule(userAgent: 'PerplexityBot', allow: ['/']),
        ];
    }

    public function id(): string
    {
        return 'robots';
    }

    public function name(): string
    {
        return 'Robots';
    }

    public function version(): string
    {
        return '0.1.0';
    }

    public function description(): string
    {
        return 'Manages robots.txt, including AI crawler directives.';
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

    /**
     * Honestly empty: docs/07's Audit Rules list (robots.txt exists,
     * no duplicate user agents, sitemap exists, etc.) has nothing to
     * report into yet — no Audit Engine exists to consume rule ids.
     */
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
                id: 'robots-txt',
                type: 'robots-txt',
                location: '/robots.txt',
                status: 'active',
                version: $this->version(),
                module: $this->id(),
                health: 'healthy',
                dependencies: [],
                source: 'robots',
                lastChecked: gmdate('c')
            ),
        ];
    }

    /**
     * Real (not fake) syntax check on the rule set actually
     * generate()'d: no two rules may target the same user-agent. Always
     * passes against the current fixed default (which has none), but
     * genuinely re-evaluates whatever `$this->rules` holds.
     */
    public function validate(DiscoveredResource $resource): ValidationResult
    {
        $start = microtime(true);

        $userAgents = array_map(static fn (RobotsRule $rule): string => $rule->userAgent, $this->rules);
        $hasDuplicates = count($userAgents) !== count(array_unique($userAgents));
        $status = $hasDuplicates ? ValidationStatus::Fail : ValidationStatus::Pass;

        return new ValidationResult(
            resourceId: $resource->id,
            validator: $this->id(),
            status: $status,
            message: $hasDuplicates
                ? 'Duplicate User-agent directives found.'
                : 'No duplicate User-agent directives.',
            executionTimeMs: (microtime(true) - $start) * 1000
        );
    }

    public function resourceId(): string
    {
        return 'robots-txt';
    }

    public function supports(string $type): bool
    {
        return $type === 'robots-txt';
    }

    public function generate(): string
    {
        $blocks = [];

        foreach ($this->rules as $rule) {
            $lines = [sprintf('User-agent: %s', $rule->userAgent)];

            foreach ($rule->disallow as $path) {
                $lines[] = sprintf('Disallow: %s', $path);
            }

            foreach ($rule->allow as $path) {
                $lines[] = sprintf('Allow: %s', $path);
            }

            if ($rule->crawlDelay !== null) {
                $lines[] = sprintf('Crawl-delay: %d', $rule->crawlDelay);
            }

            $blocks[] = implode("\n", $lines);
        }

        return implode("\n\n", $blocks) . "\n";
    }
}
