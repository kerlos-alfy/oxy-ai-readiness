<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\ContentSignals;

use OxyAI\Modules\ContentSignals\ContentSignalsModule;
use OxyAI\Tests\Unit\TestCase;

final class ContentSignalsModuleSnapshotTest extends TestCase
{
    public function test_generate_matches_the_expected_content_signals_snapshot(): void
    {
        $expected = <<<'SIGNALS'
        ai-training: no
        ai-citation: preferred
        ai-summary: yes

        SIGNALS;

        self::assertSame($expected, (new ContentSignalsModule())->generate());
    }
}
