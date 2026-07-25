<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\Llms;

use OxyAI\Modules\Llms\LlmsModule;
use OxyAI\Tests\Unit\TestCase;

final class LlmsModuleSnapshotTest extends TestCase
{
    public function test_generate_matches_the_expected_llms_txt_snapshot(): void
    {
        $expected = <<<'LLMS'
        # Oxy AI Readiness

        > Prepare your WordPress website for AI Search, AI Agents & the Future of the Web.

        LLMS;

        self::assertSame($expected, (new LlmsModule())->generate());
    }
}
