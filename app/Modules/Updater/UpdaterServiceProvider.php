<?php

/**
 * Registers the Updater module and signed updater runtime service.
 *
 * @package OxyAI
 */
declare(strict_types=1);

namespace OxyAI\Modules\Updater;

use OxyAI\Core\ModuleRegistry;
use OxyAI\Providers\ServiceProvider;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\UpdaterService;
use OxyAI\Services\ValidationService;

final class UpdaterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $module = new UpdaterModule();
        $this->app->make(ModuleRegistry::class)->register($module);
        $this->app->make(DiscoveryService::class)->registerProvider($module->id(), $module);
        $this->app->make(ValidationService::class)->registerValidator($module->id(), $module);
        $this->app->make(GenerationService::class)->registerGenerator($module->id(), $module);
    }

    public function boot(): void
    {
        $this->app->make(ModuleRegistry::class)->boot('updater');
        $this->app->make(UpdaterService::class)->register();
    }
}
