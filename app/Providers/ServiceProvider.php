<?php

/**
 * Base class for Core and Module service providers.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Providers;

use OxyAI\Core\Application;

/**
 * Per the Service Container pattern in docs/29-Developer-Guide.md:
 * register() binds services into the Application's Container; boot()
 * runs runtime behavior (e.g. hook registration) once every provider
 * has registered. No heavy operations belong in register().
 *
 * docs/29-Developer-Guide.md's worked example imports this class as
 * `OxyAI\Core\Container\ServiceProvider`; that conflicts with
 * docs/04-Folder-Structure.md's flat `Core/Container.php` file, since a
 * class and a namespace can't share one name under PSR-4. Treated as
 * illustrative rather than literal (same precedent as Phase 1's
 * RepositoryInterface decision) and placed here instead, matching the
 * already-documented top-level `Providers/` folder in the App
 * structure section of docs/04-Folder-Structure.md. Logged in
 * DECISIONS.md.
 */
abstract class ServiceProvider
{
    public function __construct(protected readonly Application $app)
    {
    }

    abstract public function register(): void;

    public function boot(): void
    {
    }
}
