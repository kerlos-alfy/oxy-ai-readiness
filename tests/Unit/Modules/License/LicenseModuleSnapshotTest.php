<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\License;

use OxyAI\Modules\License\LicenseModule;
use OxyAI\Tests\Unit\TestCase;

final class LicenseModuleSnapshotTest extends TestCase
{
    public function test_generate_matches_the_expected_license_status_snapshot(): void
    {
        $expected = <<<'JSON'
        {
            "tier": "free",
            "activated": false,
            "supports": {
                "agency": false,
                "enterprise": false,
                "offline_validation": false
            }
        }
        JSON;

        self::assertSame($expected, (new LicenseModule())->generate());
    }
}
