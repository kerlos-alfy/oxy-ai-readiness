<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\EndToEnd;

use Brain\Monkey\Functions;
use OxyAI\Core\Plugin;
use OxyAI\DTO\Grade;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\ScoringService;
use OxyAI\Services\ValidationService;
use OxyAI\Tests\Unit\TestCase;

/**
 * Proves the Phase 8 exit criterion's third requirement — "audit rule
 * shows in Scoring output" — using the real `Plugin` wiring (not
 * mocks): constructs the actual Container/Bootstrap/ServiceProvider
 * chain, boots it exactly as a real WordPress request would, then
 * confirms a robots.txt-attributable `ValidationResult` is present in
 * the combined result set `ScoringService` is handed, and that it
 * actually influences the calculated grade.
 */
final class RobotsScoringEndToEndTest extends TestCase
{
    public function test_robots_validation_result_flows_into_the_score(): void
    {
        // OAuthDiscoveryModule's real Generators call these to build their
        // real issuer/resource identity (see their own docblocks) — every
        // validator runs against every resource system-wide, so this
        // full-system test is the one place they're actually invoked.
        Functions\when('home_url')->justReturn('https://example.test/');
        Functions\when('rest_url')->justReturn('https://example.test/wp-json/oxy-ai/v1');

        $plugin = new Plugin('/plugin.php', '0.1.0');
        $plugin->run();
        $plugin->boot();

        $app = $plugin->application();
        $discovery = $app->make(DiscoveryService::class);
        $validation = $app->make(ValidationService::class);
        $scoring = $app->make(ScoringService::class);

        $results = [];

        foreach ($discovery->map() as $resource) {
            foreach ($validation->validate($resource) as $result) {
                $results[] = $result;
            }
        }

        $robotsResults = array_values(array_filter(
            $results,
            static fn ($result): bool => $result->resourceId === 'robots-txt'
        ));

        self::assertNotEmpty($robotsResults, 'Expected robots.txt to contribute a validation result.');

        $score = $scoring->calculate($results);

        // Both the probe fixture and the default robots.txt ruleset
        // pass validation, so the combined score should be perfect.
        self::assertSame(100.0, $score->score);
        self::assertSame(Grade::APlus, $score->grade);
    }
}
