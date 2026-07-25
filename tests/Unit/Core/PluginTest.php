<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Core;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use OxyAI\Core\Config;
use OxyAI\Core\ModuleRegistry;
use OxyAI\Core\Plugin;
use OxyAI\Tests\Unit\TestCase;

final class PluginTest extends TestCase
{
    public function test_constructing_binds_config_as_a_container_singleton(): void
    {
        $plugin = new Plugin('/plugins/oxy-ai-readiness/oxy-ai-readiness.php', '0.1.0');

        $config = $plugin->application()->make(Config::class);

        self::assertInstanceOf(Config::class, $config);
        self::assertSame('0.1.0', $config->version());
        self::assertSame($config, $plugin->application()->make(Config::class));
    }

    public function test_run_registers_the_kernel_on_plugins_loaded(): void
    {
        $plugin = new Plugin('/plugin.php', '0.1.0');

        $plugin->run();

        self::assertTrue(Actions\has('plugins_loaded'));
    }

    public function test_run_then_boot_registers_and_boots_the_probe_module_end_to_end(): void
    {
        $plugin = new Plugin('/plugin.php', '0.1.0');

        $plugin->run();
        $plugin->boot();

        $registry = $plugin->application()->make(ModuleRegistry::class);

        self::assertTrue($registry->has('probe'));
        self::assertTrue($registry->isBooted('probe'));
    }

    public function test_activate_records_installed_at_only_when_not_already_set_and_always_updates_version(): void
    {
        Functions\expect('get_option')
            ->once()
            ->andReturnUsing(static fn (string $key, mixed $default = false): mixed => $default);

        Functions\expect('update_option')
            ->twice()
            ->andReturn(true);

        Functions\expect('wp_mkdir_p')
            ->once()
            ->with('/storage/generated')
            ->andReturn(true);

        $plugin = new Plugin('/plugin.php', '0.1.0');

        $this->expectNotToPerformAssertions();

        $plugin->activate();
    }

    public function test_activate_does_not_overwrite_an_existing_installed_at(): void
    {
        Functions\expect('get_option')
            ->once()
            ->andReturn('2026-01-01T00:00:00+00:00');

        Functions\expect('update_option')
            ->once()
            ->with('oxy_ai_version', '0.1.0', false)
            ->andReturn(true);

        Functions\expect('wp_mkdir_p')->once()->andReturn(true);

        $plugin = new Plugin('/plugin.php', '0.1.0');

        $this->expectNotToPerformAssertions();

        $plugin->activate();
    }

    public function test_activate_network_wide_on_a_single_site_install_only_activates_once(): void
    {
        Functions\expect('is_multisite')->once()->andReturn(false);
        Functions\expect('get_sites')->never();
        Functions\expect('switch_to_blog')->never();
        Functions\expect('restore_current_blog')->never();

        Functions\expect('get_option')
            ->once()
            ->andReturnUsing(static fn (string $key, mixed $default = false): mixed => $default);
        Functions\expect('update_option')->twice()->andReturn(true);
        Functions\expect('wp_mkdir_p')->once()->andReturn(true);

        $plugin = new Plugin('/plugin.php', '0.1.0');

        $this->expectNotToPerformAssertions();

        $plugin->activate(true);
    }

    public function test_activate_network_wide_on_multisite_activates_every_site(): void
    {
        Functions\expect('is_multisite')->once()->andReturn(true);
        Functions\expect('get_sites')->once()->with(['fields' => 'ids'])->andReturn([1, 2, 3]);
        Functions\expect('switch_to_blog')->times(3);
        Functions\expect('restore_current_blog')->times(3);

        Functions\expect('get_option')
            ->times(3)
            ->andReturnUsing(static fn (string $key, mixed $default = false): mixed => $default);
        Functions\expect('update_option')->times(6)->andReturn(true);
        Functions\expect('wp_mkdir_p')->once()->andReturn(true);

        $plugin = new Plugin('/plugin.php', '0.1.0');

        $this->expectNotToPerformAssertions();

        $plugin->activate(true);
    }

    public function test_deactivate_is_a_safe_no_op(): void
    {
        $plugin = new Plugin('/plugin.php', '0.1.0');

        $plugin->deactivate();

        $this->expectNotToPerformAssertions();
    }

    public function test_deactivate_accepts_network_wide_and_is_still_a_safe_no_op(): void
    {
        $plugin = new Plugin('/plugin.php', '0.1.0');

        $plugin->deactivate(true);

        $this->expectNotToPerformAssertions();
    }
}
