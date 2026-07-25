<?php

/**
 * Registers the plugin's REST routes on WordPress's `rest_api_init`.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Core;

use OxyAI\Providers\ServiceProvider;

/**
 * Fills in the "Register REST API" step of docs/02-Architecture.md's
 * Bootstrap Sequence — the first phase with any REST routes to
 * register. Route files are plain PHP files under `routes/` returning
 * a closure, per docs/29-Developer-Guide.md's "Adding A REST Route"
 * convention; loading them through the shared `Hooks` wrapper (rather
 * than calling `add_action('rest_api_init', ...)` directly) keeps
 * every hook the plugin registers going through one inspectable path,
 * consistent with `Hooks`' stated purpose since Phase 2.
 */
final class RestServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $app = $this->app;

        $this->app->make(Hooks::class)->addAction('rest_api_init', static function () use ($app): void {
            $registerRoutes = require dirname(__DIR__, 2) . '/routes/api.php';
            $registerRoutes($app);
        });
    }
}
