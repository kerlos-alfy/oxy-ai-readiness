<?php

/**
 * Runs the plugin's boot sequence.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Core;

use OxyAI\Providers\ServiceProvider;

/**
 * Covers the part of the Bootstrap Sequence in docs/02-Architecture.md
 * this phase can meaningfully implement: run every Provider's
 * register() (bindings only, per docs/29-Developer-Guide.md), then
 * every Provider's boot() (runtime behavior — Module/Standard
 * registration and booting happens here), mark the Application ready,
 * and fire the plugin-ready event. "Register REST API" and "Load Admin
 * Interface" still have nothing to do, since no REST routes or admin
 * UI exist yet. Idempotent: a second call is a no-op.
 */
final class Bootstrap
{
    private const READY_EVENT = 'oxy_ai_ready';

    /**
     * @param array<int, ServiceProvider> $providers Run in array order;
     *        every register() call happens before any boot(), so a
     *        later Provider can depend on an earlier one's bindings.
     */
    public function __construct(
        private readonly Application $app,
        private readonly array $providers = []
    ) {
    }

    public function run(): void
    {
        if ($this->app->isBooted()) {
            return;
        }

        foreach ($this->providers as $provider) {
            $provider->register();
        }

        foreach ($this->providers as $provider) {
            $provider->boot();
        }

        $this->app->markBooted();

        do_action(self::READY_EVENT, $this->app);
    }
}
