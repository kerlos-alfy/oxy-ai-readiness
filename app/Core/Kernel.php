<?php

/**
 * WordPress-facing entry point for the plugin's boot timing.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Core;

/**
 * The only Core class that decides *when* the plugin boots. It
 * registers the plugin on WordPress's `plugins_loaded` hook and
 * delegates to Bootstrap once WordPress fires it, keeping "what boot
 * does" (Bootstrap) separate from "when boot happens" (Kernel).
 */
final class Kernel
{
    private const BOOT_HOOK = 'plugins_loaded';

    public function __construct(
        private readonly Bootstrap $bootstrap,
        private readonly Hooks $hooks
    ) {
    }

    public function register(): void
    {
        $this->hooks->addAction(self::BOOT_HOOK, [$this, 'boot']);
    }

    public function boot(): void
    {
        $this->bootstrap->run();
    }
}
