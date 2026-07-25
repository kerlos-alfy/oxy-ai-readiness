<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Core;

use OxyAI\Core\Config;
use OxyAI\Tests\Unit\TestCase;

final class ConfigTest extends TestCase
{
    public function test_exposes_version_file_and_default_text_domain(): void
    {
        $config = new Config('0.1.0', '/var/www/wp-content/plugins/oxy-ai-readiness/oxy-ai-readiness.php');

        self::assertSame('0.1.0', $config->version());
        self::assertSame(
            '/var/www/wp-content/plugins/oxy-ai-readiness/oxy-ai-readiness.php',
            $config->pluginFile()
        );
        self::assertSame('oxy-ai-readiness', $config->textDomain());
    }

    public function test_plugin_dir_is_the_trailing_slashed_directory_of_the_plugin_file(): void
    {
        $config = new Config('0.1.0', '/var/www/plugins/oxy-ai-readiness/oxy-ai-readiness.php');

        self::assertSame('/var/www/plugins/oxy-ai-readiness/', $config->pluginDir());
    }

    public function test_text_domain_can_be_overridden(): void
    {
        $config = new Config('0.1.0', '/plugin.php', 'custom-domain');

        self::assertSame('custom-domain', $config->textDomain());
    }
}
