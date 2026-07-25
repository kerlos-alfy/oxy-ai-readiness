<?php

/**
 * The llms.txt AI Standard, owned by the LLMS module.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\Llms;

use OxyAI\Contracts\StandardInterface;
use OxyAI\Exceptions\ModuleException;

/**
 * Per ADR-001's ownership table: LLMS owns the "llms.txt" Standard.
 * Same delegation shape as `Modules/Robots/RobotsStandard`.
 */
final class LlmsStandard implements StandardInterface
{
    public function __construct(private readonly LlmsModule $module)
    {
    }

    public function id(): string
    {
        return 'llms-txt';
    }

    public function name(): string
    {
        return 'llms.txt';
    }

    public function version(): string
    {
        return '1.0';
    }

    public function specification(): string
    {
        return 'https://llmstxt.org/';
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
            throw new ModuleException('LLMS module has no discovered resource to validate.');
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
        return new ModuleException(sprintf('LLMS module has no %s registered yet.', $what));
    }
}
