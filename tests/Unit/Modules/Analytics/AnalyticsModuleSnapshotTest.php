<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\Analytics;

use OxyAI\Modules\Analytics\AnalyticsModule;
use OxyAI\Tests\Unit\TestCase;

final class AnalyticsModuleSnapshotTest extends TestCase
{
    public function test_generate_matches_the_expected_analytics_summary_snapshot(): void
    {
        $expected = <<<'JSON'
        {
            "period": "daily",
            "metrics": [
                "ai_crawlers",
                "visits",
                "markdown_requests",
                "llms_requests",
                "agent_requests"
            ],
            "counts": {
                "ai_crawlers": 0,
                "visits": 0,
                "markdown_requests": 0,
                "llms_requests": 0,
                "agent_requests": 0
            }
        }
        JSON;

        self::assertSame($expected, (new AnalyticsModule())->generate());
    }
}
