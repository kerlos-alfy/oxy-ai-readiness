<?php

/**
 * Central Discovery Engine: builds the Discovery Map.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Services;

use OxyAI\Contracts\DiscoveryInterface;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\Exceptions\ModuleException;

/**
 * Per docs/14-Discovery-Engine.md: instead of every module implementing
 * its own discovery logic, modules register a `DiscoveryInterface`
 * provider here, and this service runs all of them to build one
 * Discovery Map. Read-only: nothing here writes to the filesystem,
 * database, or WordPress options — it only aggregates what registered
 * providers report about themselves.
 *
 * The map is built lazily on first access (`map()`/`resources()`
 * trigger a scan if none has run yet) rather than eagerly during
 * Bootstrap, keeping "Register Services"/"Load Core Components" light
 * per docs/27-Performance-Spec.md and CLAUDE.md's "do not run heavy
 * operations during frontend requests." No REST route mutates state —
 * `reset()` exists for internal/test use only this phase; a secured
 * POST endpoint is deferred (see DECISIONS.md).
 */
final class DiscoveryService
{
    /** @var array<string, DiscoveryInterface> */
    private array $providers = [];

    /** @var array<string, DiscoveredResource>|null */
    private ?array $map = null;

    public function registerProvider(string $moduleId, DiscoveryInterface $provider): void
    {
        if (isset($this->providers[$moduleId])) {
            throw new ModuleException(sprintf('Discovery provider for "%s" is already registered.', $moduleId));
        }

        $this->providers[$moduleId] = $provider;
    }

    public function scan(): void
    {
        do_action('oxy_ai_discovery_started');

        $map = [];

        foreach ($this->providers as $provider) {
            foreach ($provider->discover() as $resource) {
                $map[$resource->id] = $resource;

                do_action('oxy_ai_resource_discovered', $resource);
            }
        }

        $this->map = $map;

        do_action('oxy_ai_discovery_finished', $map);
    }

    /** @return array<string, DiscoveredResource> */
    public function map(): array
    {
        if ($this->map === null) {
            $this->scan();
        }

        return $this->map ?? [];
    }

    /** @return array<int, DiscoveredResource> */
    public function resources(): array
    {
        return array_values($this->map());
    }

    public function reset(): void
    {
        $this->map = null;
    }
}
