<?php

/**
 * Registers the API Catalog module into every engine it participates in.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\ApiCatalog;

use OxyAI\Core\ModuleRegistry;
use OxyAI\Core\StandardsRegistry;
use OxyAI\Providers\ServiceProvider;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\ValidationService;

/**
 * Mirrors `Modules/Robots/RobotsServiceProvider`'s wiring exactly.
 */
final class ApiCatalogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $module = new ApiCatalogModule();

        $this->app->make(ModuleRegistry::class)->register($module);
        $this->app->make(DiscoveryService::class)->registerProvider($module->id(), $module);
        $this->app->make(ValidationService::class)->registerValidator($module->id(), $module);
        $this->app->make(GenerationService::class)->registerGenerator($module->id(), $module);
        $this->app->make(StandardsRegistry::class)->register(new ApiCatalogStandard($module));
    }

    public function boot(): void
    {
        $this->app->make(ModuleRegistry::class)->boot('api-catalog');
    }
}
