<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Admin;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use OxyAI\Admin\AdminServiceProvider;
use OxyAI\Core\Application;
use OxyAI\Core\Config;
use OxyAI\Core\Container;
use OxyAI\Core\Hooks;
use OxyAI\Repositories\FileRepository;
use OxyAI\Tests\Unit\Support\InMemoryFilesystem;
use OxyAI\Tests\Unit\TestCase;
use RuntimeException;

final class AdminServiceProviderTest extends TestCase
{
    private const PLUGIN_FILE = '/plugin/oxy-ai-readiness.php';
    private const DIST_DIR = '/plugin/dist';
    private const MANIFEST_ABSOLUTE_PATH = self::DIST_DIR . '/.vite/manifest.json';

    private function makeApp(): Application
    {
        $app = new Application(new Container());
        $app->singleton(Config::class, static fn (): Config => new Config('0.1.0', self::PLUGIN_FILE));
        $app->singleton(Hooks::class, static fn (): Hooks => new Hooks());

        return $app;
    }

    private function bootProvider(Application $app, ?FileRepository $distRepository = null): AdminServiceProvider
    {
        $provider = new AdminServiceProvider($app, $distRepository);
        $provider->register();
        $provider->boot();

        return $provider;
    }

    /**
     * @return array<int, array{hook: string, callback: callable, priority: int, args: int}>
     */
    private function actionsOf(Application $app): array
    {
        return $app->make(Hooks::class)->registeredActions();
    }

    private function callbackFor(Application $app, string $hook): callable
    {
        foreach ($this->actionsOf($app) as $action) {
            if ($action['hook'] === $hook) {
                return $action['callback'];
            }
        }

        throw new RuntimeException(sprintf('No action registered for hook "%s".', $hook));
    }

    public function test_register_is_a_safe_no_op(): void
    {
        $provider = new AdminServiceProvider($this->makeApp());
        $provider->register();

        $this->expectNotToPerformAssertions();
    }

    public function test_boot_registers_the_admin_menu_enqueue_and_script_tag_hooks(): void
    {
        $app = $this->makeApp();
        $this->bootProvider($app);

        $hooks = array_column($this->actionsOf($app), 'hook');

        self::assertContains('admin_menu', $hooks);
        self::assertContains('admin_enqueue_scripts', $hooks);
        self::assertTrue(Actions\has('admin_menu'));
        self::assertTrue(Actions\has('admin_enqueue_scripts'));

        $filters = $app->make(Hooks::class)->registeredFilters();
        self::assertSame('script_loader_tag', $filters[0]['hook']);
    }

    public function test_admin_menu_callback_registers_exactly_one_top_level_page(): void
    {
        $app = $this->makeApp();
        $provider = $this->bootProvider($app);

        Functions\when('__')->returnArg(1);
        Functions\expect('add_menu_page')
            ->once()
            ->with(
                'Oxy AI Readiness',
                'AI Readiness',
                'manage_options',
                'oxy-ai-readiness',
                [$provider, 'renderRoot'],
                'dashicons-superhero-alt',
                80
            )
            ->andReturn('toplevel_page_oxy-ai-readiness');

        ($this->callbackFor($app, 'admin_menu'))();

        $this->expectNotToPerformAssertions();
    }

    public function test_enqueue_callback_does_nothing_on_a_different_admin_page(): void
    {
        $app = $this->makeApp();
        $this->bootProvider($app);

        Functions\expect('wp_enqueue_script')->never();

        ($this->callbackFor($app, 'admin_enqueue_scripts'))('some-other-page');

        $this->expectNotToPerformAssertions();
    }

    public function test_enqueue_callback_shows_an_admin_notice_when_the_spa_has_not_been_built(): void
    {
        $app = $this->makeApp();
        $this->bootProvider($app, new FileRepository(self::DIST_DIR, new InMemoryFilesystem()));

        Functions\expect('wp_enqueue_script')->never();

        ($this->callbackFor($app, 'admin_enqueue_scripts'))('toplevel_page_oxy-ai-readiness');

        $noticeHooks = array_column(
            array_filter(
                $this->actionsOf($app),
                static fn (array $action): bool => $action['hook'] === 'admin_notices'
            ),
            'hook'
        );

        self::assertNotEmpty($noticeHooks);
    }

    public function test_enqueue_callback_enqueues_the_built_bundle_and_localizes_rest_config(): void
    {
        $filesystem = new InMemoryFilesystem();
        $filesystem->put_contents(self::MANIFEST_ABSOLUTE_PATH, wp_json_encode([
            'assets/react/main.tsx' => [
                'file' => 'assets/main-abc123.js',
                'css' => ['assets/main-abc123.css'],
            ],
        ]));

        $app = $this->makeApp();
        $this->bootProvider($app, new FileRepository(self::DIST_DIR, $filesystem));

        Functions\expect('plugins_url')
            ->once()
            ->with('dist/', self::PLUGIN_FILE)
            ->andReturn('https://example.test/wp-content/plugins/oxy-ai-readiness/dist/');

        Functions\expect('wp_enqueue_script')
            ->once()
            ->with(
                'oxy-ai-readiness-app',
                'https://example.test/wp-content/plugins/oxy-ai-readiness/dist/assets/main-abc123.js',
                [],
                '0.1.0',
                true
            );

        Functions\expect('wp_enqueue_style')
            ->once()
            ->with(
                'oxy-ai-readiness-app',
                'https://example.test/wp-content/plugins/oxy-ai-readiness/dist/assets/main-abc123.css',
                [],
                '0.1.0'
            );

        Functions\when('esc_url_raw')->returnArg();
        Functions\expect('rest_url')->once()->with('oxy-ai/v1')->andReturn('https://example.test/wp-json/oxy-ai/v1');
        Functions\expect('wp_create_nonce')->once()->with('wp_rest')->andReturn('nonce-123');

        Functions\expect('wp_localize_script')
            ->once()
            ->with('oxy-ai-readiness-app', 'oxyAiReadiness', [
                'restUrl' => 'https://example.test/wp-json/oxy-ai/v1',
                'nonce' => 'nonce-123',
                'version' => '0.1.0',
            ]);

        ($this->callbackFor($app, 'admin_enqueue_scripts'))('toplevel_page_oxy-ai-readiness');

        $this->expectNotToPerformAssertions();
    }

    public function test_render_root_prints_the_spa_mount_node(): void
    {
        Functions\when('esc_attr')->returnArg();

        $provider = new AdminServiceProvider($this->makeApp());

        $this->expectOutputString('<div id="oxy-ai-readiness-root"></div>');

        $provider->renderRoot();
    }

    public function test_render_missing_build_notice_prints_an_error_notice(): void
    {
        Functions\when('esc_html__')->returnArg(1);

        $provider = new AdminServiceProvider($this->makeApp());

        $this->expectOutputRegex('/notice-error/');

        $provider->renderMissingBuildNotice();
    }

    public function test_script_loader_tag_filter_marks_only_this_plugins_script_as_a_module(): void
    {
        $app = $this->makeApp();
        $this->bootProvider($app);

        $filter = $app->make(Hooks::class)->registeredFilters()[0]['callback'];

        // phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript -- fixture markup for the string-rewrite filter under test, not real plugin output.
        self::assertSame(
            '<script type="module" src="app.js"></script>',
            $filter('<script src="app.js"></script>', 'oxy-ai-readiness-app')
        );

        self::assertSame(
            '<script src="other.js"></script>',
            $filter('<script src="other.js"></script>', 'some-other-handle')
        );
        // phpcs:enable
    }
}
