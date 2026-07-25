<?php

/**
 * Registers the Headers module into every engine it participates in.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\Headers;

use OxyAI\Core\ModuleRegistry;
use OxyAI\Providers\ServiceProvider;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\ValidationService;

/**
 * Mirrors `Modules/Robots/RobotsServiceProvider`, except it does not
 * register a Standard — per ADR-001's ownership table, Headers is one
 * of the modules that explicitly owns no Standard ("No Standard:
 * Dashboard, Audit, Headers, Settings, Logs, ..."), unlike Robots/LLMS/
 * Markdown/Content Signals.
 */
final class HeadersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $module = new HeadersModule();

        $this->app->make(ModuleRegistry::class)->register($module);
        $this->app->make(DiscoveryService::class)->registerProvider($module->id(), $module);
        $this->app->make(ValidationService::class)->registerValidator($module->id(), $module);
        $this->app->make(GenerationService::class)->registerGenerator($module->id(), $module);
    }

    public function boot(): void
    {
        $this->app->make(ModuleRegistry::class)->boot('headers');
    }
}
