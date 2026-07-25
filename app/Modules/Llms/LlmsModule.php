<?php

/**
 * The LLMS module: manages llms.txt.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\Llms;

use OxyAI\Contracts\DiscoveryInterface;
use OxyAI\Contracts\GeneratorInterface;
use OxyAI\Contracts\ModuleInterface;
use OxyAI\Contracts\ValidatorInterface;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;

/**
 * Per docs/08-LLMS-Spec.md and the Phase 11 roadmap row ("each repeats
 * the Phase 8 pattern now that the pipeline is proven"). Scoped exactly
 * like `Modules/Robots`: real, deterministic default content and real
 * validation logic — not the document's full aspirational feature set
 * (auto-detected page prioritization, multi-language support, a visual
 * builder), which needs real WordPress content to discover/rank and a
 * Settings/DB-persistence phase and the Admin UI phase this project
 * hasn't reached.
 *
 * The default content is llms.txt's own real, minimal, standards-
 * compliant shape (docs/08's "File Structure": Title, Description) —
 * a level-1 heading plus a blockquote description, using the plugin's
 * own real product identity (docs/01-Vision.md's Plugin Name/Tagline)
 * rather than fabricated page content this project has no real pages
 * to source from yet.
 */
final class LlmsModule implements ModuleInterface, DiscoveryInterface, ValidatorInterface, GeneratorInterface
{
    private const TITLE = 'Oxy AI Readiness';
    private const DESCRIPTION = 'Prepare your WordPress website for AI Search, AI Agents & the Future of the Web.';

    public function id(): string
    {
        return 'llms';
    }

    public function name(): string
    {
        return 'LLMS';
    }

    public function version(): string
    {
        return '0.1.0';
    }

    public function description(): string
    {
        return 'Generates and validates llms.txt.';
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
                id: 'llms-txt',
                type: 'llms-txt',
                location: '/llms.txt',
                status: 'active',
                version: $this->version(),
                module: $this->id(),
                health: 'healthy',
                dependencies: [],
                source: 'llms',
                lastChecked: gmdate('c')
            ),
        ];
    }

    /**
     * Real syntax check: llms.txt must have a title (`# `) and a
     * description blockquote (`> `) — docs/08's Audit Rules list
     * "Organization Present"/"Description Present" narrowed to what's
     * checkable from the file content alone.
     */
    public function validate(DiscoveredResource $resource): ValidationResult
    {
        $start = microtime(true);
        $content = $this->generate();

        $hasTitle = str_starts_with($content, '# ');
        $hasDescription = str_contains($content, '> ');
        $status = $hasTitle && $hasDescription ? ValidationStatus::Pass : ValidationStatus::Fail;

        return new ValidationResult(
            resourceId: $resource->id,
            validator: $this->id(),
            status: $status,
            message: $status === ValidationStatus::Pass
                ? 'Title and description are present.'
                : 'Missing title or description.',
            executionTimeMs: (microtime(true) - $start) * 1000
        );
    }

    public function resourceId(): string
    {
        return 'llms-txt';
    }

    public function supports(string $type): bool
    {
        return $type === 'llms-txt';
    }

    public function generate(): string
    {
        return sprintf("# %s\n\n> %s\n", self::TITLE, self::DESCRIPTION);
    }
}
