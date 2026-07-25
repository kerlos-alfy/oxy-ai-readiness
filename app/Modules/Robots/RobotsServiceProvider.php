<?php

/**
 * Registers the Robots module into every engine it participates in.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\Robots;

use OxyAI\Core\ModuleRegistry;
use OxyAI\Core\StandardsRegistry;
use OxyAI\Providers\ServiceProvider;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\ValidationService;

/**
 * The per-module `{ModuleName}ServiceProvider` half of the canonical
 * module template (docs/04-Folder-Structure.md, ADR-002). Mirrors
 * `Modules/Probe/ProbeServiceProvider`'s wiring shape exactly — this is
 * the first *real* module doing so, proving the pattern generalizes
 * beyond the internal probe.
 */
final class RobotsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $module = new RobotsModule();

        $this->app->make(ModuleRegistry::class)->register($module);
        $this->app->make(DiscoveryService::class)->registerProvider($module->id(), $module);
        $this->app->make(ValidationService::class)->registerValidator($module->id(), $module);
        $this->app->make(GenerationService::class)->registerGenerator($module->id(), $module);
        $this->app->make(StandardsRegistry::class)->register(new RobotsStandard($module));
    }

    public function boot(): void
    {
        $this->app->make(ModuleRegistry::class)->boot('robots');
    }
}
