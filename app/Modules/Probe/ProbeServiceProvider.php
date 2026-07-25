<?php

/**
 * Registers the Probe module and standard into their registries.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\Probe;

use OxyAI\Core\ModuleRegistry;
use OxyAI\Core\StandardsRegistry;
use OxyAI\Providers\ServiceProvider;

/**
 * The per-module `{ModuleName}ServiceProvider` half of the canonical
 * module template (docs/04-Folder-Structure.md, ADR-002). Requires
 * `CoreServiceProvider` to have already bound `ModuleRegistry`/
 * `StandardsRegistry` — Bootstrap runs every provider's register()
 * before any boot(), and lists Core's provider first.
 */
final class ProbeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->make(ModuleRegistry::class)->register(new ProbeModule());
        $this->app->make(StandardsRegistry::class)->register(new ProbeStandard());
    }

    public function boot(): void
    {
        $this->app->make(ModuleRegistry::class)->boot('probe');
    }
}
