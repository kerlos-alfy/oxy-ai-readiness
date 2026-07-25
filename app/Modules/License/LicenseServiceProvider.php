<?php

/**
 * Registers the License module into every engine it participates in.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\License;

use OxyAI\Core\ModuleRegistry;
use OxyAI\Providers\ServiceProvider;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\ValidationService;

/**
 * Mirrors `Modules/Headers/HeadersServiceProvider` — no Standard is
 * registered, per ADR-001's ownership table listing License among the
 * modules owning none.
 */
final class LicenseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $module = new LicenseModule();

        $this->app->make(ModuleRegistry::class)->register($module);
        $this->app->make(DiscoveryService::class)->registerProvider($module->id(), $module);
        $this->app->make(ValidationService::class)->registerValidator($module->id(), $module);
        $this->app->make(GenerationService::class)->registerGenerator($module->id(), $module);
    }

    public function boot(): void
    {
        $this->app->make(ModuleRegistry::class)->boot('license');
    }
}
