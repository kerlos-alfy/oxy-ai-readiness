<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\AgentSkills;

use OxyAI\Modules\AgentSkills\AgentSkillsModule;
use OxyAI\Tests\Unit\TestCase;

final class AgentSkillsModuleSnapshotTest extends TestCase
{
    public function test_generate_matches_the_expected_skill_registry_snapshot(): void
    {
        $expected = <<<'JSON'
        [
            {
                "id": "get-ai-readiness-score",
                "name": "Get AI Readiness Score",
                "description": "Returns the site's current AI readiness score, grade, confidence, and trend.",
                "category": "knowledge",
                "status": "enabled",
                "authentication": "wordpress-login",
                "endpoint": {
                    "method": "GET",
                    "path": "\/wp-json\/oxy-ai\/v1\/score"
                }
            },
            {
                "id": "run-ai-readiness-audit",
                "name": "Run AI Readiness Audit",
                "description": "Runs a fresh audit scan and returns a structured report of validation results.",
                "category": "knowledge",
                "status": "enabled",
                "authentication": "wordpress-login",
                "endpoint": {
                    "method": "POST",
                    "path": "\/wp-json\/oxy-ai\/v1\/audit\/start"
                }
            },
            {
                "id": "get-ai-readiness-recommendations",
                "name": "Get AI Readiness Recommendations",
                "description": "Returns actionable recommendations for improving AI readiness.",
                "category": "knowledge",
                "status": "enabled",
                "authentication": "wordpress-login",
                "endpoint": {
                    "method": "GET",
                    "path": "\/wp-json\/oxy-ai\/v1\/recommendations"
                }
            }
        ]
        JSON;

        self::assertSame($expected, (new AgentSkillsModule())->generate());
    }
}
