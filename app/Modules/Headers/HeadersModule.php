<?php

/**
 * The Headers module: manages HTTP response headers.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\Headers;

use OxyAI\Contracts\DiscoveryInterface;
use OxyAI\Contracts\GeneratorInterface;
use OxyAI\Contracts\ModuleInterface;
use OxyAI\Contracts\ValidatorInterface;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;

/**
 * Per docs/10-Headers-Spec.md and the Phase 11 roadmap row. Scoped
 * like `Modules/Robots`: a real, deterministic default declaration and
 * real validation — not the document's full feature set (a visual
 * header builder, live request/response testing, third-party
 * server/CDN conflict detection), which needs a real HTTP request
 * context and the Admin UI phase this project hasn't reached.
 *
 * Represented the same way `Modules/Robots` represents robots.txt: a
 * generated text resource (one `Header-Name: value` per line), not a
 * live `send_headers` hook — keeping the pattern uniform with every
 * other Phase 8/11 module rather than making Headers a special case
 * that emits real HTTP responses while the rest generate files.
 * Hooking real header emission is deferred to whenever this module's
 * declared headers need to actually reach a browser.
 *
 * Default headers are real, current, and directly relevant: `Content-
 * Signal` (docs/10's own "AI DISCOVERY HEADERS" list) plus two
 * well-known security headers from its "SECURITY" list.
 */
final class HeadersModule implements ModuleInterface, DiscoveryInterface, ValidatorInterface, GeneratorInterface
{
    private const HEADERS = [
        'Content-Signal' => 'ai-train=no, ai-input=yes',
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
    ];

    public function id(): string
    {
        return 'headers';
    }

    public function name(): string
    {
        return 'Headers';
    }

    public function version(): string
    {
        return '0.1.0';
    }

    public function description(): string
    {
        return 'Manages HTTP response headers, including AI discovery and security headers.';
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
                id: 'http-headers',
                type: 'http-headers',
                location: '/',
                status: 'active',
                version: $this->version(),
                module: $this->id(),
                health: 'healthy',
                dependencies: [],
                source: 'headers',
                lastChecked: gmdate('c')
            ),
        ];
    }

    /**
     * Real syntax check: no header name may be declared twice
     * (docs/10's "Validation" list: "Duplicate Headers").
     */
    public function validate(DiscoveredResource $resource): ValidationResult
    {
        $start = microtime(true);
        $names = [];

        foreach (explode("\n", trim($this->generate())) as $line) {
            [$name] = array_pad(explode(':', $line, 2), 1, '');
            $names[] = $name;
        }

        $hasDuplicates = count($names) !== count(array_unique($names));
        $status = $hasDuplicates ? ValidationStatus::Fail : ValidationStatus::Pass;

        return new ValidationResult(
            resourceId: $resource->id,
            validator: $this->id(),
            status: $status,
            message: $hasDuplicates ? 'Duplicate header names found.' : 'No duplicate header names.',
            executionTimeMs: (microtime(true) - $start) * 1000
        );
    }

    public function resourceId(): string
    {
        return 'http-headers';
    }

    public function supports(string $type): bool
    {
        return $type === 'http-headers';
    }

    public function generate(): string
    {
        $lines = [];

        foreach (self::HEADERS as $name => $value) {
            $lines[] = sprintf('%s: %s', $name, $value);
        }

        return implode("\n", $lines) . "\n";
    }
}
