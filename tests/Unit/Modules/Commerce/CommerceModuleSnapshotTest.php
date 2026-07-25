<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\Commerce;

use OxyAI\Modules\Commerce\CommerceModule;
use OxyAI\Tests\Unit\TestCase;

final class CommerceModuleSnapshotTest extends TestCase
{
    public function test_generate_matches_the_expected_commerce_status_snapshot(): void
    {
        $expected = <<<'JSON'
        {
            "woocommerce_active": false,
            "supports": {
                "x402": false,
                "machine_payments": false,
                "ai_checkout": false,
                "agent_purchases": false
            }
        }
        JSON;

        self::assertSame($expected, (new CommerceModule())->generate());
    }
}
