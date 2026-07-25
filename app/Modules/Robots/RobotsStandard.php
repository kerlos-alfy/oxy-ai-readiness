<?php

/**
 * The robots.txt AI Standard, owned by the Robots module.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\Robots;

use OxyAI\Contracts\StandardInterface;
use OxyAI\Exceptions\ModuleException;

/**
 * Per ADR-001's ownership table: Robots owns the "robots.txt" Standard.
 * Same delegation shape as `Modules/Probe/ProbeStandard`:
 * discover()/validate()/generate() delegate to the owning module for
 * real; score()/monitor()/report() throw, since no Scoring/Monitoring/
 * Reporting engine gives a Standard a per-module contribution to
 * delegate to yet.
 */
final class RobotsStandard implements StandardInterface
{
    public function __construct(private readonly RobotsModule $module)
    {
    }

    public function id(): string
    {
        return 'robots-txt';
    }

    public function name(): string
    {
        return 'robots.txt';
    }

    public function version(): string
    {
        return '1.0';
    }

    public function specification(): string
    {
        return 'https://www.robotstxt.org/robotstxt.html';
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
            throw new ModuleException('Robots module has no discovered resource to validate.');
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
        return new ModuleException(sprintf('Robots module has no %s registered yet.', $what));
    }
}
