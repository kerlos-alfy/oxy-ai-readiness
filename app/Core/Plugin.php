<?php

/**
 * Top-level plugin object.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Core;

use OxyAI\Repositories\OptionsRepository;

/**
 * Instantiated once by oxy-ai-readiness.php on every request. Owns the
 * plugin's WordPress lifecycle (activation/deactivation) and
 * constructs the Container/Application/Kernel chain from
 * docs/02-Architecture.md's Bootstrap Sequence.
 */
final class Plugin
{
    private readonly Config $config;
    private readonly Application $app;
    private readonly Kernel $kernel;

    public function __construct(string $pluginFile, string $version)
    {
        $this->config = new Config($version, $pluginFile);

        $container = new Container();
        $container->singleton(Config::class, fn (): Config => $this->config);

        $this->app = new Application($container);

        $bootstrap = new Bootstrap($this->app);
        $this->kernel = new Kernel($bootstrap, new Hooks());
    }

    public function run(): void
    {
        $this->kernel->register();
    }

    /**
     * Uses OptionsRepository (Phase 1) for exactly the narrow use case
     * its own docblock describes: install timestamp and installed
     * version. No `oxy_*` tables, migrations, or module state exist
     * yet, so there is nothing else for activation to do this phase.
     */
    public function activate(): void
    {
        $options = new OptionsRepository();

        if (!$options->has('installed_at')) {
            $options->set('installed_at', gmdate('c'));
        }

        $options->set('version', $this->config->version());
    }

    /**
     * No scheduled events, transients, or caches exist yet to tear
     * down; this callback exists because WordPress requires one to be
     * registered, not to fake work that isn't there.
     */
    public function deactivate(): void
    {
    }

    public function application(): Application
    {
        return $this->app;
    }
}
