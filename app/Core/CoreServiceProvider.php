<?php

/**
 * Registers Core-level singletons into the Container.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Core;

use OxyAI\Providers\ServiceProvider;
use OxyAI\Repositories\FileRepository;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\ValidationService;

/**
 * The first real consumer of the `ServiceProvider` pattern introduced
 * in Phase 2: binds `ModuleRegistry`/`StandardsRegistry`/
 * `DiscoveryService`/`ValidationService`/`GenerationService` as
 * Container singletons so every later Module ServiceProvider can
 * resolve the same shared instances. No runtime behavior belongs in
 * `boot()` here — registering Modules/Standards/Discovery providers/
 * Validators/Generators into these is each owning Module's own
 * ServiceProvider's job (see `Modules/Probe/ProbeServiceProvider`), not
 * Core's.
 */
final class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleRegistry::class, static fn (): ModuleRegistry => new ModuleRegistry());
        $this->app->singleton(
            StandardsRegistry::class,
            static fn (): StandardsRegistry => new StandardsRegistry()
        );
        $this->app->singleton(
            DiscoveryService::class,
            static fn (): DiscoveryService => new DiscoveryService()
        );
        $this->app->singleton(
            ValidationService::class,
            static fn (): ValidationService => new ValidationService()
        );

        $this->app->singleton(GenerationService::class, function (): GenerationService {
            $config = $this->app->make(Config::class);
            $files = new FileRepository($config->pluginDir() . 'storage/generated');

            return new GenerationService(
                $this->app->make(ValidationService::class),
                $this->app->make(DiscoveryService::class),
                $files
            );
        });
    }
}
