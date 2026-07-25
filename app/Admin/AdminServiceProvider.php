<?php

/**
 * Mounts the React admin SPA into wp-admin.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Admin;

use OxyAI\Core\Application;
use OxyAI\Core\Config;
use OxyAI\Core\Hooks;
use OxyAI\Providers\ServiceProvider;
use OxyAI\Repositories\FileRepository;

/**
 * Per docs/03-UI.md ("must feel like a premium SaaS application rather
 * than a traditional WordPress plugin") the admin UI is one centralized
 * React SPA (docs/04-Folder-Structure.md line 271), not per-page
 * server-rendered views — so this provider only ever registers a single
 * top-level menu page whose body is an empty mount node; `assets/react`
 * (built by Vite into `dist/`) owns all in-page navigation between
 * Dashboard/Audit/module screens itself (see `App.tsx`/`Sidebar.tsx`).
 * docs/04-Folder-Structure.md's aspirational `Admin/{Dashboard,Pages,
 * Settings,Widgets,Views,Controllers,Middleware}` breakdown describes
 * per-page PHP views, which contradicts that same document's own SPA
 * note — treated as aspirational rather than literal, same precedent as
 * every prior phase's documented deviations. See DECISIONS.md.
 *
 * Reads Vite's build manifest (`dist/.vite/manifest.json`, per Vite 5's
 * default `build.manifest` location — see `vite.config.ts`) so enqueued
 * filenames are never hardcoded, then localizes `window.oxyAiReadiness`
 * exactly as `assets/react/Utils/api.ts` declares it.
 */
final class AdminServiceProvider extends ServiceProvider
{
    private const MENU_SLUG = 'oxy-ai-readiness';
    private const PAGE_HOOK_SUFFIX = 'toplevel_page_' . self::MENU_SLUG;
    private const ROOT_ELEMENT_ID = 'oxy-ai-readiness-root';
    private const SCRIPT_HANDLE = 'oxy-ai-readiness-app';
    private const STYLE_HANDLE = 'oxy-ai-readiness-app';
    private const VITE_ENTRY = 'assets/react/main.tsx';
    private const MANIFEST_RELATIVE_PATH = '.vite/manifest.json';

    private FileRepository $distRepository;
    private Hooks $hooks;

    /**
     * `$distRepository` is only ever passed explicitly by tests — real
     * boot always builds it from the plugin's own `dist/` directory, so
     * it stays out of the constructor signature every other Provider
     * shares.
     */
    public function __construct(Application $app, ?FileRepository $distRepository = null)
    {
        parent::__construct($app);

        if ($distRepository instanceof FileRepository) {
            $this->distRepository = $distRepository;
        }
    }

    public function register(): void
    {
        if (isset($this->distRepository)) {
            return;
        }

        $config = $this->app->make(Config::class);
        $this->distRepository = new FileRepository($config->pluginDir() . 'dist');
    }

    public function boot(): void
    {
        $this->hooks = $this->app->make(Hooks::class);

        $this->hooks->addAction('admin_menu', function (): void {
            add_menu_page(
                __('Oxy AI Readiness', 'oxy-ai-readiness'),
                __('AI Readiness', 'oxy-ai-readiness'),
                'manage_options',
                self::MENU_SLUG,
                [$this, 'renderRoot'],
                'dashicons-superhero-alt',
                80
            );
        });

        $this->hooks->addAction('admin_enqueue_scripts', function (string $hookSuffix): void {
            $this->enqueueAssets($hookSuffix);
        }, 10, 1);

        $this->hooks->addFilter('script_loader_tag', function (string $tag, string $handle): string {
            return $this->markAsModule($tag, $handle);
        }, 10, 2);
    }

    public function renderRoot(): void
    {
        printf('<div id="%s"></div>', esc_attr(self::ROOT_ELEMENT_ID));
    }

    public function renderMissingBuildNotice(): void
    {
        printf(
            '<div class="notice notice-error"><p>%s</p></div>',
            esc_html__('Oxy AI Readiness: the admin UI has not been built yet. Run npm run build.', 'oxy-ai-readiness')
        );
    }

    private function markAsModule(string $tag, string $handle): string
    {
        if ($handle !== self::SCRIPT_HANDLE) {
            return $tag;
        }

        return str_replace(' src=', ' type="module" src=', $tag);
    }

    private function enqueueAssets(string $hookSuffix): void
    {
        if ($hookSuffix !== self::PAGE_HOOK_SUFFIX) {
            return;
        }

        $entry = $this->manifestEntry();

        if ($entry === null) {
            $this->hooks->addAction('admin_notices', [$this, 'renderMissingBuildNotice']);

            return;
        }

        $config = $this->app->make(Config::class);
        $distUrl = plugins_url('dist/', $config->pluginFile());

        wp_enqueue_script(self::SCRIPT_HANDLE, $distUrl . $entry['file'], [], $config->version(), true);

        /** @var array<int, string> $cssFiles */
        $cssFiles = $entry['css'] ?? [];

        foreach ($cssFiles as $cssFile) {
            wp_enqueue_style(self::STYLE_HANDLE, $distUrl . $cssFile, [], $config->version());
        }

        wp_localize_script(self::SCRIPT_HANDLE, 'oxyAiReadiness', [
            'restUrl' => esc_url_raw(rest_url('oxy-ai/v1')),
            'nonce' => wp_create_nonce('wp_rest'),
            'version' => $config->version(),
        ]);
    }

    /**
     * @return array{file: string, css?: array<int, string>}|null
     */
    private function manifestEntry(): ?array
    {
        $manifestJson = $this->distRepository->read(self::MANIFEST_RELATIVE_PATH);

        if ($manifestJson === null) {
            return null;
        }

        $manifest = json_decode($manifestJson, true);

        if (!is_array($manifest) || !isset($manifest[self::VITE_ENTRY]['file'])) {
            return null;
        }

        /** @var array{file: string, css?: array<int, string>} $entry */
        $entry = $manifest[self::VITE_ENTRY];

        return $entry;
    }
}
