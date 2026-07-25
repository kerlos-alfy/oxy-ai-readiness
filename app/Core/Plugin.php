<?php

/**
 * Top-level plugin object.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Core;

use OxyAI\Admin\AdminServiceProvider;
use OxyAI\Modules\AgentSkills\AgentSkillsServiceProvider;
use OxyAI\Modules\Analytics\AnalyticsServiceProvider;
use OxyAI\Modules\ApiCatalog\ApiCatalogServiceProvider;
use OxyAI\Modules\Commerce\CommerceServiceProvider;
use OxyAI\Modules\ContentSignals\ContentSignalsServiceProvider;
use OxyAI\Modules\Headers\HeadersServiceProvider;
use OxyAI\Modules\License\LicenseServiceProvider;
use OxyAI\Modules\Llms\LlmsServiceProvider;
use OxyAI\Modules\Markdown\MarkdownServiceProvider;
use OxyAI\Modules\Mcp\McpServiceProvider;
use OxyAI\Modules\OAuthDiscovery\OAuthDiscoveryServiceProvider;
use OxyAI\Modules\Probe\ProbeServiceProvider;
use OxyAI\Modules\Robots\RobotsServiceProvider;
use OxyAI\Modules\Updater\UpdaterServiceProvider;
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
            new McpServiceProvider($this->app),
            new AgentSkillsServiceProvider($this->app),
            new ApiCatalogServiceProvider($this->app),
            new OAuthDiscoveryServiceProvider($this->app),
            new CommerceServiceProvider($this->app),
            new AnalyticsServiceProvider($this->app),
            new LicenseServiceProvider($this->app),
            new UpdaterServiceProvider($this->app),
            new AdminServiceProvider($this->app),
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
     * a fresh install where that folder has never been created. This
     * is filesystem-level (one shared plugin install), so it runs once
     * regardless of how many sites get activated below — never per-site.
     *
     * `$networkWide` is WordPress's own second argument to an
     * `activate_{plugin}` hook (passed automatically to any registered
     * callback that accepts it — see `register_activation_hook()`'s own
     * behavior, not a parameter this project invented). Per
     * docs/28-Testing-Strategy.md's Supported Environment Matrix
     * ("Network Activated," "Per-Site Activated"): when a super admin
     * network-activates the plugin, WordPress does *not* iterate every
     * site's context on its own — a plugin that wants every site's
     * `oxy_ai_installed_at`/`oxy_ai_version` options set (not just
     * whichever site happened to be "current" during the network-admin
     * request) must do that itself, via `switch_to_blog()` per site.
     * Without this, only one site in the network would ever get real
     * install-tracking options — silently wrong on the one Environment
     * Matrix mode ("Network Activated") this project hadn't verified.
     */
    public function activate(bool $networkWide = false): void
    {
        if ($networkWide && is_multisite()) {
            foreach (get_sites(['fields' => 'ids']) as $siteId) {
                switch_to_blog((int) $siteId);
                $this->activateCurrentSite();
                restore_current_blog();
            }
        } else {
            $this->activateCurrentSite();
        }

        wp_mkdir_p($this->config->pluginDir() . 'storage/generated');
    }

    private function activateCurrentSite(): void
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
     * registered, not to fake work that isn't there. Still accepts
     * `$networkWide` (WordPress passes it automatically, same as
     * `activate()`) so a future real teardown can use it without
     * changing this method's signature again.
     */
    public function deactivate(bool $networkWide = false): void
    {
    }

    public function application(): Application
    {
        return $this->app;
    }
}
