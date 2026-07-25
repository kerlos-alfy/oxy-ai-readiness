<?php

/**
 * Registers the Analytics module into every engine it participates in.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\Analytics;

use OxyAI\Core\ModuleRegistry;
use OxyAI\Providers\ServiceProvider;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\ValidationService;

/**
 * Mirrors `Modules/Headers/HeadersServiceProvider` — no Standard is
 * registered, per ADR-001's ownership table listing Analytics among
 * the modules owning none.
 */
final class AnalyticsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $module = new AnalyticsModule();

        $this->app->make(ModuleRegistry::class)->register($module);
        $this->app->make(DiscoveryService::class)->registerProvider($module->id(), $module);
        $this->app->make(ValidationService::class)->registerValidator($module->id(), $module);
        $this->app->make(GenerationService::class)->registerGenerator($module->id(), $module);
    }

    public function boot(): void
    {
        $this->app->make(ModuleRegistry::class)->boot('analytics');
    }
}
