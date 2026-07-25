<?php

/**
 * The Content Signals module: declares site-wide AI usage signals.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\ContentSignals;

use OxyAI\Contracts\DiscoveryInterface;
use OxyAI\Contracts\GeneratorInterface;
use OxyAI\Contracts\ModuleInterface;
use OxyAI\Contracts\ValidatorInterface;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;

/**
 * Per docs/11-Content-Signals-Spec.md and the Phase 11 roadmap row.
 * Scoped like `Modules/Robots`: this phase declares real, site-wide
 * **AI usage signals** (docs/11's own "AI USAGE SIGNALS" category —
 * AI Training Allowed/Restricted/Prohibited, AI Citation Preferred/
 * Optional/Disabled, AI Summary Allowed) — matching the real-world
 * Content Signals policy concept this document models (a site-level
 * declaration, conceptually similar to the `Content-Signal` HTTP
 * header the Headers module also emits). Not per-page Identity/
 * Purpose/Audience/Trust/Knowledge signals — those need real page
 * content and entity extraction this project has neither of yet.
 */
final class ContentSignalsModule implements ModuleInterface, DiscoveryInterface, ValidatorInterface, GeneratorInterface
{
    private const SIGNALS = [
        'ai-training' => 'no',
        'ai-citation' => 'preferred',
        'ai-summary' => 'yes',
    ];

    public function id(): string
    {
        return 'content-signals';
    }

    public function name(): string
    {
        return 'Content Signals';
    }

    public function version(): string
    {
        return '0.1.0';
    }

    public function description(): string
    {
        return 'Declares and validates site-wide AI content usage signals.';
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
                id: 'content-signals',
                type: 'content-signals',
                location: '/',
                status: 'active',
                version: $this->version(),
                module: $this->id(),
                health: 'healthy',
                dependencies: [],
                source: 'content-signals',
                lastChecked: gmdate('c')
            ),
        ];
    }

    /**
     * Real check: no signal key may be declared twice — docs/11's
     * Validation list: "Duplicate Signals".
     */
    public function validate(DiscoveredResource $resource): ValidationResult
    {
        $start = microtime(true);
        $keys = [];

        foreach (explode("\n", trim($this->generate())) as $line) {
            [$key] = array_pad(explode(':', $line, 2), 1, '');
            $keys[] = $key;
        }

        $hasDuplicates = count($keys) !== count(array_unique($keys));
        $status = $hasDuplicates ? ValidationStatus::Fail : ValidationStatus::Pass;

        return new ValidationResult(
            resourceId: $resource->id,
            validator: $this->id(),
            status: $status,
            message: $hasDuplicates ? 'Duplicate signal keys found.' : 'No duplicate signal keys.',
            executionTimeMs: (microtime(true) - $start) * 1000
        );
    }

    public function resourceId(): string
    {
        return 'content-signals';
    }

    public function supports(string $type): bool
    {
        return $type === 'content-signals';
    }

    public function generate(): string
    {
        $lines = [];

        foreach (self::SIGNALS as $key => $value) {
            $lines[] = sprintf('%s: %s', $key, $value);
        }

        return implode("\n", $lines) . "\n";
    }
}
