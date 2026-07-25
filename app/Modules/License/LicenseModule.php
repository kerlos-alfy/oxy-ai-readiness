<?php

/**
 * The License module: reports the plugin's current license tier.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\License;

use OxyAI\Contracts\DiscoveryInterface;
use OxyAI\Contracts\GeneratorInterface;
use OxyAI\Contracts\ModuleInterface;
use OxyAI\Contracts\ValidatorInterface;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;

/**
 * Per docs/05-Modules.md's License module ("Commercial Version" —
 * Activation, Updates, Subscriptions, Agency, Enterprise, Offline
 * Validation) and `.project/09-Canonical-Architecture.md`'s ownership
 * table (License owns no Standard). No license server, activation API,
 * or paid tier exists anywhere in this project — this build is free
 * and unactivated. Declaring `"tier": "pro"` or a fake activation
 * state would be exactly the fabricated data CLAUDE.md prohibits;
 * this module instead reports its own real, current, honest state.
 * See DECISIONS.md.
 */
final class LicenseModule implements ModuleInterface, DiscoveryInterface, ValidatorInterface, GeneratorInterface
{
    public function id(): string
    {
        return 'license';
    }

    public function name(): string
    {
        return 'License';
    }

    public function version(): string
    {
        return '0.1.0';
    }

    public function description(): string
    {
        return "Reports the plugin's current license tier and activation state.";
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
                id: 'license-status',
                type: 'license-status',
                location: '/.well-known/oxy-license-status',
                status: 'active',
                version: $this->version(),
                module: $this->id(),
                health: 'healthy',
                dependencies: [],
                source: 'license',
                lastChecked: gmdate('c')
            ),
        ];
    }

    /**
     * Real schema check: valid JSON with the fields every consumer
     * (Dashboard, future Recommendation gating) needs to rely on.
     */
    public function validate(DiscoveredResource $resource): ValidationResult
    {
        $start = microtime(true);
        $decoded = json_decode($this->generate(), true);
        $valid = is_array($decoded) && isset($decoded['tier'], $decoded['activated'], $decoded['supports']);

        return new ValidationResult(
            resourceId: $resource->id,
            validator: $this->id(),
            status: $valid ? ValidationStatus::Pass : ValidationStatus::Fail,
            message: $valid ? 'License status declaration is well-formed.' : 'License status declaration is malformed.',
            executionTimeMs: (microtime(true) - $start) * 1000
        );
    }

    public function resourceId(): string
    {
        return 'license-status';
    }

    public function supports(string $type): bool
    {
        return $type === 'license-status';
    }

    public function generate(): string
    {
        return (string) wp_json_encode([
            'tier' => 'free',
            'activated' => false,
            'supports' => [
                'agency' => false,
                'enterprise' => false,
                'offline_validation' => false,
            ],
        ], JSON_PRETTY_PRINT);
    }
}
