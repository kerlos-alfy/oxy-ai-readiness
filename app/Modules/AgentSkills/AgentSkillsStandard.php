<?php

/**
 * The Agent Skills AI Standard, owned by the Agent Skills module.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\AgentSkills;

use OxyAI\Contracts\StandardInterface;
use OxyAI\Exceptions\ModuleException;

/**
 * Per ADR-001's ownership table: Agent Skills owns the "Agent Skills"
 * Standard. Same delegation shape as `Modules/Robots/RobotsStandard`.
 */
final class AgentSkillsStandard implements StandardInterface
{
    public function __construct(private readonly AgentSkillsModule $module)
    {
    }

    public function id(): string
    {
        return 'agent-skills';
    }

    public function name(): string
    {
        return 'Agent Skills';
    }

    public function version(): string
    {
        return '1.0';
    }

    /**
     * Agent Skills, like Content Signals, is a newer concept with no
     * single confidently-known canonical spec page — a descriptive
     * identifier rather than a guessed external URL.
     */
    public function specification(): string
    {
        return 'agent-skills/0.1';
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
            throw new ModuleException('Agent Skills module has no discovered resource to validate.');
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
        return new ModuleException(sprintf('Agent Skills module has no %s registered yet.', $what));
    }
}
