<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\Updater;

use OxyAI\Modules\Updater\UpdaterModule;
use OxyAI\Tests\Unit\TestCase;

final class UpdaterModuleSnapshotTest extends TestCase
{
    public function test_generate_matches_the_expected_updater_status_snapshot(): void
    {
        $expected = <<<'JSON'
        {
            "current_version": "0.1.0",
            "channel": "stable",
            "update_available": false
        }
        JSON;

        self::assertSame($expected, (new UpdaterModule())->generate());
    }
}
