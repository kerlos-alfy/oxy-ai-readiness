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
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\ValidationService;

/**
 * The per-module `{ModuleName}ServiceProvider` half of the canonical
 * module template (docs/04-Folder-Structure.md, ADR-002). Requires
 * `CoreServiceProvider` to have already bound `ModuleRegistry`/
 * `StandardsRegistry`/`DiscoveryService`/`ValidationService`/
 * `GenerationService` — Bootstrap runs every provider's register()
 * before any boot(), and lists Core's provider first.
 */
final class ProbeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $module = new ProbeModule();

        $this->app->make(ModuleRegistry::class)->register($module);
        $this->app->make(DiscoveryService::class)->registerProvider($module->id(), $module);
        $this->app->make(ValidationService::class)->registerValidator($module->id(), $module);
        $this->app->make(GenerationService::class)->registerGenerator($module->id(), $module);
        $this->app->make(StandardsRegistry::class)->register(new ProbeStandard($module));
    }

    public function boot(): void
    {
        $this->app->make(ModuleRegistry::class)->boot('probe');
    }
}
