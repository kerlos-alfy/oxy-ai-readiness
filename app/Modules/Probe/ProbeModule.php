<?php

/**
 * Internal module validating the Module SDK skeleton.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\Probe;

use OxyAI\Contracts\DiscoveryInterface;
use OxyAI\Contracts\ModuleInterface;
use OxyAI\DTO\DiscoveredResource;

/**
 * The "one minimal internal 'probe' module ... for validation only (not
 * user-facing)" named in the Phase 3 roadmap entry. Exists to prove
 * ModuleRegistry's register/boot/enable/disable lifecycle works
 * end-to-end, both under test and wired for real into Bootstrap. It is
 * inert in every real sense: no asset pipeline, REST router, Settings
 * Manager, or Audit Engine exists yet to register into, so
 * assets()/routes()/settings()/permissions()/audit() honestly return
 * nothing rather than fabricate placeholder entries.
 *
 * Also implements DiscoveryInterface (Phase 4): registers one fixture
 * DiscoveredResource with DiscoveryService, proving the Discovery
 * pipeline end-to-end per the Phase 4 exit criterion ("Discovery Map
 * correctly lists a known fixture resource") without discovering any
 * real site data.
 */
final class ProbeModule implements ModuleInterface, DiscoveryInterface
{
    public function id(): string
    {
        return 'probe';
    }

    public function name(): string
    {
        return 'Probe';
    }

    public function version(): string
    {
        return '0.1.0';
    }

    public function description(): string
    {
        return 'Internal module validating the Module & Standard SDK skeleton. Not user-facing.';
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
                id: 'probe-fixture',
                type: 'internal-fixture',
                location: 'internal://probe',
                status: 'active',
                version: $this->version(),
                module: $this->id(),
                health: 'healthy',
                dependencies: [],
                source: 'probe',
                lastChecked: gmdate('c')
            ),
        ];
    }
}
