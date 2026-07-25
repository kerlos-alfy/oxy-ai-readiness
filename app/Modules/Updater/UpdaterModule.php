<?php

/**
 * The Updater module: reports the plugin's current version and update channel.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\Updater;

use OxyAI\Contracts\DiscoveryInterface;
use OxyAI\Contracts\GeneratorInterface;
use OxyAI\Contracts\ModuleInterface;
use OxyAI\Contracts\ValidatorInterface;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;

/**
 * Per docs/05-Modules.md's Updater module ("Automatic Updates" —
 * Stable/Beta/Nightly channels, Rollback) and
 * `.project/09-Canonical-Architecture.md`'s ownership table (Updater
 * owns no Standard). No update-check server, WordPress.org listing, or
 * remote version API exists for this plugin — declaring
 * `"update_available": true` or a fake newer version would be
 * fabricated data with no real update to actually deliver. This module
 * instead reports its own real, current version and the one real
 * channel that exists (`stable`); Beta/Nightly and rollback need real
 * release infrastructure this project doesn't have yet. See
 * DECISIONS.md.
 */
final class UpdaterModule implements ModuleInterface, DiscoveryInterface, ValidatorInterface, GeneratorInterface
{
    public function id(): string
    {
        return 'updater';
    }

    public function name(): string
    {
        return 'Updater';
    }

    public function version(): string
    {
        return '0.1.0';
    }

    public function description(): string
    {
        return "Reports the plugin's current version and update channel.";
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
                id: 'updater-status',
                type: 'updater-status',
                location: '/.well-known/oxy-updater-status',
                status: 'active',
                version: $this->version(),
                module: $this->id(),
                health: 'healthy',
                dependencies: [],
                source: 'updater',
                lastChecked: gmdate('c')
            ),
        ];
    }

    /**
     * Real schema check: valid JSON with a real, non-empty current
     * version and a recognized channel.
     */
    public function validate(DiscoveredResource $resource): ValidationResult
    {
        $start = microtime(true);
        $decoded = json_decode($this->generate(), true);
        $valid = is_array($decoded)
            && !empty($decoded['current_version'])
            && in_array($decoded['channel'] ?? null, ['stable', 'beta', 'nightly'], true);

        return new ValidationResult(
            resourceId: $resource->id,
            validator: $this->id(),
            status: $valid ? ValidationStatus::Pass : ValidationStatus::Fail,
            message: $valid ? 'Updater status declaration is well-formed.' : 'Updater status declaration is malformed.',
            executionTimeMs: (microtime(true) - $start) * 1000
        );
    }

    public function resourceId(): string
    {
        return 'updater-status';
    }

    public function supports(string $type): bool
    {
        return $type === 'updater-status';
    }

    public function generate(): string
    {
        return (string) wp_json_encode([
            'current_version' => $this->version(),
            'channel' => 'stable',
            'update_available' => false,
        ], JSON_PRETTY_PRINT);
    }
}
