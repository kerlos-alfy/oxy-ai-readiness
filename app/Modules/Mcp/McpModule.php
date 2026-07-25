<?php

/**
 * The MCP module: publishes the site's Model Context Protocol server card.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\Mcp;

use OxyAI\Contracts\DiscoveryInterface;
use OxyAI\Contracts\GeneratorInterface;
use OxyAI\Contracts\ModuleInterface;
use OxyAI\Contracts\ValidatorInterface;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;

/**
 * Per docs/12-MCP-Spec.md and the Phase 14 roadmap row ("Each has
 * server-card/registry generation, validation, and REST per its spec
 * doc"). Scoped to the Server Card identity layer only — docs/12's own
 * "CAPABILITIES" (Resources, Tools, Prompts, Sampling, Streaming,
 * Completion, ...) describe a live MCP JSON-RPC transport this project
 * has never built (no `/mcp` protocol endpoint executes tools or
 * serves resources; only this discovery-layer REST exists). Declaring
 * those capabilities as `true` would describe infrastructure that
 * doesn't exist — exactly the "mock production data" CLAUDE.md
 * prohibits — so every capability/resource/tool/prompt in the
 * generated card is honestly empty/false until a real MCP transport is
 * built. See DECISIONS.md.
 */
final class McpModule implements ModuleInterface, DiscoveryInterface, ValidatorInterface, GeneratorInterface
{
    private const NAME = 'Oxy AI Readiness';
    private const DESCRIPTION = 'Prepare your WordPress website for AI Search, AI Agents & the Future of the Web.';
    private const ORGANIZATION = 'Oxy AI Readiness';

    public function id(): string
    {
        return 'mcp';
    }

    public function name(): string
    {
        return 'MCP';
    }

    public function version(): string
    {
        return '0.1.0';
    }

    public function description(): string
    {
        return 'Generates and validates the site\'s MCP server card.';
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
                id: 'mcp-server-card',
                type: 'mcp-server-card',
                location: '/.well-known/mcp.json',
                status: 'active',
                version: $this->version(),
                module: $this->id(),
                health: 'healthy',
                dependencies: [],
                source: 'mcp',
                lastChecked: gmdate('c')
            ),
        ];
    }

    /**
     * Real schema check: docs/12's own "VALIDATION" list (JSON
     * Validation, Required Fields) narrowed to what a static server
     * card can fail — valid JSON, and the three fields no server card
     * can be meaningful without.
     */
    public function validate(DiscoveredResource $resource): ValidationResult
    {
        $start = microtime(true);
        $decoded = json_decode($this->generate(), true);

        $missing = array_filter(
            ['name', 'version', 'description'],
            static fn (string $field): bool => !is_array($decoded) || !isset($decoded[$field])
        );

        $status = $missing === [] ? ValidationStatus::Pass : ValidationStatus::Fail;

        return new ValidationResult(
            resourceId: $resource->id,
            validator: $this->id(),
            status: $status,
            message: $missing === []
                ? 'Server card has every required field.'
                : sprintf('Server card is missing: %s.', implode(', ', $missing)),
            executionTimeMs: (microtime(true) - $start) * 1000
        );
    }

    public function resourceId(): string
    {
        return 'mcp-server-card';
    }

    public function supports(string $type): bool
    {
        return $type === 'mcp-server-card';
    }

    public function generate(): string
    {
        return (string) wp_json_encode([
            'name' => self::NAME,
            'description' => self::DESCRIPTION,
            'organization' => self::ORGANIZATION,
            'version' => $this->version(),
            'capabilities' => [
                'resources' => false,
                'tools' => false,
                'prompts' => false,
                'sampling' => false,
                'streaming' => false,
            ],
            'resources' => [],
            'tools' => [],
            'prompts' => [],
            'authentication' => ['type' => 'none'],
        ], JSON_PRETTY_PRINT);
    }
}
