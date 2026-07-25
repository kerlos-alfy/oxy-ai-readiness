<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\Headers;

use OxyAI\Modules\Headers\HeadersModule;
use OxyAI\Tests\Unit\TestCase;

final class HeadersModuleSnapshotTest extends TestCase
{
    public function test_generate_matches_the_expected_headers_snapshot(): void
    {
        $expected = <<<'HEADERS'
        Content-Signal: ai-train=no, ai-input=yes
        X-Content-Type-Options: nosniff
        Referrer-Policy: strict-origin-when-cross-origin

        HEADERS;

        self::assertSame($expected, (new HeadersModule())->generate());
    }
}
