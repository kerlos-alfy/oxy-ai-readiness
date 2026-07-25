<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\Markdown;

use OxyAI\Modules\Markdown\MarkdownModule;
use OxyAI\Tests\Unit\TestCase;

final class MarkdownModuleSnapshotTest extends TestCase
{
    public function test_generate_matches_the_expected_negotiation_declaration_snapshot(): void
    {
        $expected = <<<'MARKDOWN'
        # Markdown Negotiation

        Content-Type: text/markdown
        Accept: text/markdown, text/plain, text/html, application/json

        MARKDOWN;

        self::assertSame($expected, (new MarkdownModule())->generate());
    }
}
