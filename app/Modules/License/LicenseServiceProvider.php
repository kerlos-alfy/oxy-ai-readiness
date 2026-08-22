<?php

/**
 * Registers the License module and license runtime service.
 *
 * @package OxyAI
 */
declare(strict_types=1);

namespace OxyAI\Modules\License;

use OxyAI\Core\ModuleRegistry;
use OxyAI\Providers\ServiceProvider;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\LicenseService;
use OxyAI\Services\ValidationService;

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
        $this->app->make(LicenseService::class)->register();
    }
}
