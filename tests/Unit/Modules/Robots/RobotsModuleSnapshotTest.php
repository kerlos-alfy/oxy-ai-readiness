<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\Robots;

use OxyAI\Modules\Robots\RobotsModule;
use OxyAI\Tests\Unit\TestCase;

/**
 * The Phase 8 exit criterion names this explicitly: "snapshot test on
 * generated robots.txt." This freezes the exact expected output of the
 * fixed default rule set (docs/07-Robots-Spec.md's WordPress-standard
 * /wp-admin/ disallow plus the documented "Allow AI" template for
 * GPTBot/ChatGPT-User/Google-Extended/ClaudeBot/PerplexityBot) so any
 * accidental change to generation logic or rule content is caught.
 */
final class RobotsModuleSnapshotTest extends TestCase
{
    public function test_generate_matches_the_expected_robots_txt_snapshot(): void
    {
        $expected = <<<'ROBOTS'
        User-agent: *
        Disallow: /wp-admin/
        Allow: /wp-admin/admin-ajax.php

        User-agent: GPTBot
        Allow: /

        User-agent: ChatGPT-User
        Allow: /

        User-agent: Google-Extended
        Allow: /

        User-agent: ClaudeBot
        Allow: /

        User-agent: PerplexityBot
        Allow: /

        ROBOTS;

        $module = new RobotsModule();

        self::assertSame($expected, $module->generate());
    }
}
