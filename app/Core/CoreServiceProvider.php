<?php

/**
 * Registers Core-level singletons into the Container.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Core;

use OxyAI\Providers\ServiceProvider;

/**
 * The first real consumer of the `ServiceProvider` pattern introduced
 * in Phase 2: binds `ModuleRegistry`/`StandardsRegistry` as Container
 * singletons so every later Module/Standard ServiceProvider can
 * resolve the same shared registry instance. No runtime behavior
 * belongs in `boot()` here — registering Modules/Standards into these
 * registries is each owning Module's own ServiceProvider's job (see
 * `Modules/Probe/ProbeServiceProvider`), not Core's.
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
    }
}
