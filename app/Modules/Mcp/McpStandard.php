<?php

/**
 * The MCP AI Standard, owned by the MCP module.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\Mcp;

use OxyAI\Contracts\StandardInterface;
use OxyAI\Exceptions\ModuleException;

/**
 * Per ADR-001's ownership table: MCP owns the "MCP" Standard. Same
 * delegation shape as `Modules/Robots/RobotsStandard`.
 */
final class McpStandard implements StandardInterface
{
    public function __construct(private readonly McpModule $module)
    {
    }

    public function id(): string
    {
        return 'mcp';
    }

    public function name(): string
    {
        return 'Model Context Protocol';
    }

    public function version(): string
    {
        return '1.0';
    }

    public function specification(): string
    {
        return 'https://modelcontextprotocol.io';
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
            throw new ModuleException('MCP module has no discovered resource to validate.');
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
        return new ModuleException(sprintf('MCP module has no %s registered yet.', $what));
    }
}
