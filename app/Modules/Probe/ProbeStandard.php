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
 * Reporter — none of which exist yet (those engines are later phases),
 * so ProbeModule genuinely has none registered. Throwing here reports
 * that honestly instead of fabricating a result.
 */
final class ProbeStandard implements StandardInterface
{
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
        throw $this->noDelegate('Discovery provider');
    }

    public function generate(): mixed
    {
        throw $this->noDelegate('Generator');
    }

    public function validate(): mixed
    {
        throw $this->noDelegate('Validator');
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
