<?php

/**
 * A single robots.txt User-agent block.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\Robots;

/**
 * Fields per docs/07-Robots-Spec.md's "Robots Builder" rule shape
 * (User Agent, Allow, Disallow, Crawl Delay) — Host/Sitemap are
 * document-level directives, not per-rule, so they live on
 * `RobotsModule` itself rather than here.
 */
final class RobotsRule
{
    /**
     * @param array<int, string> $disallow
     * @param array<int, string> $allow
     */
    public function __construct(
        public readonly string $userAgent,
        public readonly array $disallow = [],
        public readonly array $allow = [],
        public readonly ?int $crawlDelay = null
    ) {
    }
}
