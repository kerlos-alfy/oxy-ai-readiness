<?php

/**
 * Top-level plugin object.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Core;

use OxyAI\Modules\ContentSignals\ContentSignalsServiceProvider;
use OxyAI\Modules\Headers\HeadersServiceProvider;
use OxyAI\Modules\Llms\LlmsServiceProvider;
use OxyAI\Modules\Markdown\MarkdownServiceProvider;
use OxyAI\Modules\Probe\ProbeServiceProvider;
use OxyAI\Modules\Robots\RobotsServiceProvider;
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

        $hooks = new Hooks();
        $container->singleton(Hooks::class, static fn (): Hooks => $hooks);

        $this->app = new Application($container);

        $providers = [
            new CoreServiceProvider($this->app),
            new RestServiceProvider($this->app),
            new ProbeServiceProvider($this->app),
            new RobotsServiceProvider($this->app),
            new LlmsServiceProvider($this->app),
            new HeadersServiceProvider($this->app),
            new MarkdownServiceProvider($this->app),
            new ContentSignalsServiceProvider($this->app),
        ];

        $bootstrap = new Bootstrap($this->app, $providers);
        $this->kernel = new Kernel($bootstrap, $hooks);
    }

    /**
     * Registers the Kernel on `plugins_loaded`. WordPress then calls
     * boot() itself when that hook fires — Brain Monkey's simulated
     * add_action()/do_action() do not actually invoke registered
     * callbacks, so tests call boot() directly to exercise the same
     * path a real WordPress request would.
     */
    public function run(): void
    {
        $this->kernel->register();
    }

    public function boot(): void
    {
        $this->kernel->boot();
    }

    /**
     * Uses OptionsRepository (Phase 1) for exactly the narrow use case
     * its own docblock describes: install timestamp and installed
     * version. No `oxy_*` tables or migrations exist yet.
     *
     * Also ensures `storage/generated/` exists: `FileRepository`
     * (Phase 1) only ever creates directories *below* its configured
     * base directory, never the base directory itself, so without this
     * every `GenerationService::publish()` call (Phase 6) would fail on
     * a fresh install where that folder has never been created.
     */
    public function activate(): void
    {
        $options = new OptionsRepository();

        if (!$options->has('installed_at')) {
            $options->set('installed_at', gmdate('c'));
        }

        $options->set('version', $this->config->version());

        wp_mkdir_p($this->config->pluginDir() . 'storage/generated');
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
