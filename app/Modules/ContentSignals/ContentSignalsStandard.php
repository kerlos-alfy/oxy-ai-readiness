<?php

/**
 * The Content Signals AI Standard, owned by the Content Signals module.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\ContentSignals;

use OxyAI\Contracts\StandardInterface;
use OxyAI\Exceptions\ModuleException;

/**
 * Per ADR-001's ownership table: Content Signals owns the "Content
 * Signals" Standard. Same delegation shape as
 * `Modules/Robots/RobotsStandard`.
 */
final class ContentSignalsStandard implements StandardInterface
{
    public function __construct(private readonly ContentSignalsModule $module)
    {
    }

    public function id(): string
    {
        return 'content-signals';
    }

    public function name(): string
    {
        return 'Content Signals';
    }

    public function version(): string
    {
        return '1.0';
    }

    /**
     * Unlike robots.txt/llms.txt (which have one well-established
     * canonical spec page), Content Signals is a newer, less
     * universally standardized concept — returning a descriptive
     * identifier here rather than guessing at a specific external URL.
     */
    public function specification(): string
    {
        return 'content-signals/1.0';
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
            throw new ModuleException('Content Signals module has no discovered resource to validate.');
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
        return new ModuleException(sprintf('Content Signals module has no %s registered yet.', $what));
    }
}
