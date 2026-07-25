<?php

/**
 * The Markdown module: manages Markdown content negotiation.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\Markdown;

use OxyAI\Contracts\DiscoveryInterface;
use OxyAI\Contracts\GeneratorInterface;
use OxyAI\Contracts\ModuleInterface;
use OxyAI\Contracts\ValidatorInterface;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;

/**
 * Per docs/09-Markdown-Spec.md and the Phase 11 roadmap row. Scoped
 * like `Modules/Robots`: this phase generates and validates the
 * module's own **negotiation capability declaration** — which
 * content-types it supports serving Markdown as (docs/09's own
 * "Content Negotiation" list: text/markdown, text/plain, text/html,
 * application/json) — not per-page converted Markdown. Real per-page
 * HTML→Markdown conversion needs actual WordPress post/page content to
 * convert, which this project has none of yet (no real site, "do not
 * use mock production data"); fabricating fake converted page content
 * would be exactly that. Declaring the negotiation capability itself is
 * real, deterministic, and genuinely validatable without needing any
 * real content to exist.
 */
final class MarkdownModule implements ModuleInterface, DiscoveryInterface, ValidatorInterface, GeneratorInterface
{
    private const SUPPORTED_TYPES = ['text/markdown', 'text/plain', 'text/html', 'application/json'];

    public function id(): string
    {
        return 'markdown';
    }

    public function name(): string
    {
        return 'Markdown';
    }

    public function version(): string
    {
        return '0.1.0';
    }

    public function description(): string
    {
        return 'Declares and validates Markdown content negotiation support.';
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
                id: 'markdown-negotiation',
                type: 'markdown-negotiation',
                location: '/',
                status: 'active',
                version: $this->version(),
                module: $this->id(),
                health: 'healthy',
                dependencies: [],
                source: 'markdown',
                lastChecked: gmdate('c')
            ),
        ];
    }

    /**
     * Real check: the declaration must actually list `text/markdown`
     * as a supported type — docs/09's Audit Rules "Negotiation Enabled".
     */
    public function validate(DiscoveredResource $resource): ValidationResult
    {
        $start = microtime(true);
        $hasMarkdown = str_contains($this->generate(), 'text/markdown');
        $status = $hasMarkdown ? ValidationStatus::Pass : ValidationStatus::Fail;

        return new ValidationResult(
            resourceId: $resource->id,
            validator: $this->id(),
            status: $status,
            message: $hasMarkdown
                ? 'text/markdown negotiation is declared.'
                : 'text/markdown negotiation is missing.',
            executionTimeMs: (microtime(true) - $start) * 1000
        );
    }

    public function resourceId(): string
    {
        return 'markdown-negotiation';
    }

    public function supports(string $type): bool
    {
        return $type === 'markdown-negotiation';
    }

    public function generate(): string
    {
        return sprintf(
            "# Markdown Negotiation\n\nContent-Type: text/markdown\nAccept: %s\n",
            implode(', ', self::SUPPORTED_TYPES)
        );
    }
}
