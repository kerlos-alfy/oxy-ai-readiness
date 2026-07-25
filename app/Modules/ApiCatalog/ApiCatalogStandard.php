<?php

/**
 * The api-catalog AI Standard, owned by the API Catalog module.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\ApiCatalog;

use OxyAI\Contracts\StandardInterface;
use OxyAI\Exceptions\ModuleException;

/**
 * Per ADR-001's ownership table: API Catalog owns the "api-catalog"
 * Standard. Same delegation shape as `Modules/Robots/RobotsStandard`.
 */
final class ApiCatalogStandard implements StandardInterface
{
    public function __construct(private readonly ApiCatalogModule $module)
    {
    }

    public function id(): string
    {
        return 'api-catalog';
    }

    public function name(): string
    {
        return 'API Catalog';
    }

    public function version(): string
    {
        return '1.0';
    }

    /**
     * Like Content Signals/Agent Skills, `.well-known/api-catalog` has
     * no single confidently-known canonical spec page — a descriptive
     * identifier rather than a guessed external URL.
     */
    public function specification(): string
    {
        return 'api-catalog/0.1';
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
            throw new ModuleException('API Catalog module has no discovered resource to validate.');
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
        return new ModuleException(sprintf('API Catalog module has no %s registered yet.', $what));
    }
}
