<?php

/**
 * Plugin-level metadata.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Core;

/**
 * Version, main plugin file path, and text domain — not to be confused
 * with the module-level `config/*.php` files in
 * docs/04-Folder-Structure.md, which belong to the modules that
 * introduce them (none exist yet, so none are created here).
 */
final class Config
{
    public function __construct(
        private readonly string $version,
        private readonly string $pluginFile,
        private readonly string $textDomain = 'oxy-ai-readiness'
    ) {
    }

    public function version(): string
    {
        return $this->version;
    }

    public function pluginFile(): string
    {
        return $this->pluginFile;
    }

    public function pluginDir(): string
    {
        return trailingslashit(dirname($this->pluginFile));
    }

    public function textDomain(): string
    {
        return $this->textDomain;
    }
}
