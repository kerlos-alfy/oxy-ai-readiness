<?php

/**
 * Registers the plugin's REST routes on WordPress's `rest_api_init`.
 *
 * @package OxyAI
 */
declare(strict_types=1);

namespace OxyAI\Core;

use OxyAI\Providers\ServiceProvider;

final class RestServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $app = $this->app;

        $this->app->make(Hooks::class)->addAction('rest_api_init', static function () use ($app): void {
            $routeFiles = ['api.php', 'updater.php'];
            foreach ($routeFiles as $routeFile) {
                $registerRoutes = require dirname(__DIR__, 2) . '/routes/' . $routeFile;
                $registerRoutes($app);
            }
        });
    }
}
