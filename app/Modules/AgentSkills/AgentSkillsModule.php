<?php

/**
 * The Agent Skills module: publishes the site's Skill Registry.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\AgentSkills;

use OxyAI\Contracts\DiscoveryInterface;
use OxyAI\Contracts\GeneratorInterface;
use OxyAI\Contracts\ModuleInterface;
use OxyAI\Contracts\ValidatorInterface;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;

/**
 * Per docs/13-Agent-Skills-Spec.md and the Phase 14 roadmap row. Docs'
 * own "DEFAULT SKILLS" list (Book Appointment, Find Doctor, Open
 * WhatsApp, ...) describes capabilities no WordPress site has out of
 * the box and this plugin doesn't build — publishing them would be
 * fabricated capability data, exactly what CLAUDE.md prohibits.
 * Instead, this registry publishes skills that are genuinely real
 * right now: this very plugin's own already-working REST actions
 * (Phases 7/9/10 — Score, Audit, Recommendations), each backed by a
 * real, callable, currently-enabled endpoint. "AI agents should
 * understand what a website can DO" (docs' own Vision) — what this
 * site can actually do today is report and improve its AI readiness,
 * so that's what's published. See DECISIONS.md.
 */
final class AgentSkillsModule implements ModuleInterface, DiscoveryInterface, ValidatorInterface, GeneratorInterface
{
    /**
     * @var array<int, array{
     *     id: string, name: string, description: string, category: string,
     *     status: string, authentication: string,
     *     endpoint: array{method: string, path: string}
     * }>
     */
    private const SKILLS = [
        [
            'id' => 'get-ai-readiness-score',
            'name' => 'Get AI Readiness Score',
            'description' => "Returns the site's current AI readiness score, grade, confidence, and trend.",
            'category' => 'knowledge',
            'status' => 'enabled',
            'authentication' => 'wordpress-login',
            'endpoint' => ['method' => 'GET', 'path' => '/wp-json/oxy-ai/v1/score'],
        ],
        [
            'id' => 'run-ai-readiness-audit',
            'name' => 'Run AI Readiness Audit',
            'description' => 'Runs a fresh audit scan and returns a structured report of validation results.',
            'category' => 'knowledge',
            'status' => 'enabled',
            'authentication' => 'wordpress-login',
            'endpoint' => ['method' => 'POST', 'path' => '/wp-json/oxy-ai/v1/audit/start'],
        ],
        [
            'id' => 'get-ai-readiness-recommendations',
            'name' => 'Get AI Readiness Recommendations',
            'description' => 'Returns actionable recommendations for improving AI readiness.',
            'category' => 'knowledge',
            'status' => 'enabled',
            'authentication' => 'wordpress-login',
            'endpoint' => ['method' => 'GET', 'path' => '/wp-json/oxy-ai/v1/recommendations'],
        ],
    ];

    public function id(): string
    {
        return 'agent-skills';
    }

    public function name(): string
    {
        return 'Agent Skills';
    }

    public function version(): string
    {
        return '0.1.0';
    }

    public function description(): string
    {
        return "Publishes the site's Agent Skill Registry.";
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
                id: 'agent-skills-registry',
                type: 'agent-skills-registry',
                location: '/.well-known/agent-skills.json',
                status: 'active',
                version: $this->version(),
                module: $this->id(),
                health: 'healthy',
                dependencies: [],
                source: 'agent-skills',
                lastChecked: gmdate('c')
            ),
        ];
    }

    /**
     * Real schema check: docs/13's own "VALIDATION" list (Schema,
     * Documentation, Examples) narrowed to what's mechanically
     * checkable — every skill has its required identity fields, and no
     * two skills share an id.
     */
    public function validate(DiscoveredResource $resource): ValidationResult
    {
        $start = microtime(true);
        $decoded = json_decode($this->generate(), true);
        $skills = is_array($decoded) ? $decoded : [];

        $ids = array_column($skills, 'id');
        $hasDuplicates = count($ids) !== count(array_unique($ids));

        $incomplete = array_filter(
            $skills,
            static fn (array $skill): bool => !isset($skill['id'], $skill['name'], $skill['description'])
        );

        $status = (!$hasDuplicates && $incomplete === [] && $skills !== [])
            ? ValidationStatus::Pass
            : ValidationStatus::Fail;

        $message = match (true) {
            $skills === [] => 'Skill registry is empty.',
            $hasDuplicates => 'Duplicate skill ids found.',
            $incomplete !== [] => 'One or more skills are missing required fields.',
            default => 'Every skill has a unique id and its required fields.',
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
        return 'agent-skills-registry';
    }

    public function supports(string $type): bool
    {
        return $type === 'agent-skills-registry';
    }

    public function generate(): string
    {
        return (string) wp_json_encode(self::SKILLS, JSON_PRETTY_PRINT);
    }
}
