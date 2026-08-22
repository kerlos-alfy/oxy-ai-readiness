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
use OxyAI\Services\LicenseService;

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

    public function run(): void
    {
        $this->kernel->register();
    }

    public function boot(): void
    {
        $this->kernel->boot();
    }

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
        $this->app->make(LicenseService::class)->activateCron();
    }

    private function activateCurrentSite(): void
    {
        $options = new OptionsRepository();
        if (!$options->has('installed_at')) {
            $options->set('installed_at', gmdate('c'));
        }

        $options->set('version', $this->config->version());
    }

    public function deactivate(bool $networkWide = false): void
    {
        $this->app->make(LicenseService::class)->deactivateCron();
    }

    public function application(): Application
    {
        return $this->app;
    }
}
