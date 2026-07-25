<?php

/**
 * Runs the plugin's boot sequence.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Core;

/**
 * Covers the part of the Bootstrap Sequence in docs/02-Architecture.md
 * this phase can meaningfully implement: mark the Application ready and
 * fire the plugin-ready event ("Register Services", "Load Core
 * Components", "Load Enabled Modules", "Register REST API", and "Load
 * Admin Interface" have nothing to do yet, since no service providers,
 * modules, REST routes, or admin UI exist — those steps activate as
 * later phases add them). Idempotent: a second call is a no-op.
 */
final class Bootstrap
{
    private const READY_EVENT = 'oxy_ai_ready';

    public function __construct(private readonly Application $app)
    {
    }

    public function run(): void
    {
        if ($this->app->isBooted()) {
            return;
        }

        $this->app->markBooted();

        do_action(self::READY_EVENT, $this->app);
    }
}
