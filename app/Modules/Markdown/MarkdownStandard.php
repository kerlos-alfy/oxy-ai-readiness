<?php

/**
 * The Markdown Negotiation AI Standard, owned by the Markdown module.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\Markdown;

use OxyAI\Contracts\StandardInterface;
use OxyAI\Exceptions\ModuleException;

/**
 * Per ADR-001's ownership table: Markdown owns the "Markdown
 * Negotiation" Standard. Same delegation shape as
 * `Modules/Robots/RobotsStandard`.
 */
final class MarkdownStandard implements StandardInterface
{
    public function __construct(private readonly MarkdownModule $module)
    {
    }

    public function id(): string
    {
        return 'markdown-negotiation';
    }

    public function name(): string
    {
        return 'Markdown Negotiation';
    }

    public function version(): string
    {
        return '1.0';
    }

    public function specification(): string
    {
        return 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Content_negotiation';
    }

    public function discover(): mixed
    {
        return $this->module->discover();
    }

    public function generate(): mixed
    {
        return $this->module->generate();
    }

    public function validate(): mixed
    {
        $resources = $this->module->discover();
        $resource = $resources[0] ?? null;

        if ($resource === null) {
            throw new ModuleException('Markdown module has no discovered resource to validate.');
        }

        return $this->module->validate($resource);
    }

    public function score(): mixed
    {
        throw $this->noDelegate('ScoreProvider');
    }

    public function monitor(): mixed
    {
        throw $this->noDelegate('Monitor');
    }

    public function report(): mixed
    {
        throw $this->noDelegate('Reporter');
    }

    public function supports(string $feature): bool
    {
        return $this->module->supports($feature);
    }

    public function migrate(string $fromVersion): void
    {
    }

    private function noDelegate(string $what): ModuleException
    {
        return new ModuleException(sprintf('Markdown module has no %s registered yet.', $what));
    }
}
