<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\Mcp;

use OxyAI\Modules\Mcp\McpModule;
use OxyAI\Tests\Unit\TestCase;

final class McpModuleSnapshotTest extends TestCase
{
    public function test_generate_matches_the_expected_server_card_snapshot(): void
    {
        $expected = <<<'JSON'
        {
            "name": "Oxy AI Readiness",
            "description": "Prepare your WordPress website for AI Search, AI Agents & the Future of the Web.",
            "organization": "Oxy AI Readiness",
            "version": "0.1.0",
            "capabilities": {
                "resources": false,
                "tools": false,
                "prompts": false,
                "sampling": false,
                "streaming": false
            },
            "resources": [],
            "tools": [],
            "prompts": [],
            "authentication": {
                "type": "none"
            }
        }
        JSON;

        self::assertSame($expected, (new McpModule())->generate());
    }
}
