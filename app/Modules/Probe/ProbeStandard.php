<?php

/**
 * Internal standard validating the Standards SDK skeleton.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\Probe;

use OxyAI\Contracts\StandardInterface;
use OxyAI\Exceptions\ModuleException;

/**
 * Owned by ProbeModule, per ADR-001 (a Standard is owned by exactly one
 * Module). Proves StandardsRegistry's register/enable/disable works
 * end-to-end. discover()/generate()/validate()/score()/monitor()/
 * report() are meant to delegate to the owning Module's registered
 * Discovery provider/Generator/Validator/ScoreProvider/Monitor/
 * Reporter.
 *
 * As of Phases 4–6, ProbeModule genuinely has a Discovery provider, a
 * Validator, and a Generator (it implements all three interfaces), so
 * discover()/validate()/generate() now delegate to it for real, per the
 * expectation set in Phase 3's own decision log ("their Standard
 * delegate methods stop throwing once their owning Module actually
 * registers a Generator/Validator/etc."). score()/monitor()/report()
 * still throw — Scoring, Monitoring, and Reporting engines don't exist
 * yet.
 */
final class ProbeStandard implements StandardInterface
{
    public function __construct(private readonly ProbeModule $module)
    {
    }

    public function id(): string
    {
        return 'probe';
    }

    public function name(): string
    {
        return 'Probe';
    }

    public function version(): string
    {
        return '0.1.0';
    }

    public function specification(): string
    {
        return 'internal-probe/0.1.0';
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
            throw new ModuleException('Probe module has no discovered resource to validate.');
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
        return false;
    }

    public function migrate(string $fromVersion): void
    {
    }

    private function noDelegate(string $what): ModuleException
    {
        return new ModuleException(sprintf('Probe module has no %s registered yet.', $what));
    }
}
