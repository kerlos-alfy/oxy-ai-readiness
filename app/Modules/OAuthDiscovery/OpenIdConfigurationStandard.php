<?php

/**
 * The openid-configuration AI Standard, owned by the OAuth Discovery module.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\OAuthDiscovery;

use OxyAI\Contracts\StandardInterface;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\Exceptions\ModuleException;

/**
 * Per ADR-001's ownership table: OAuth Discovery owns the
 * "openid-configuration" Standard (one of three — see
 * `OAuthDiscoveryModule`'s own docblock for why one module owns three
 * Standard classes here instead of the usual one).
 */
final class OpenIdConfigurationStandard implements StandardInterface
{
    public function __construct(
        private readonly OAuthDiscoveryModule $module,
        private readonly OpenIdConfigurationGenerator $generator
    ) {
    }

    public function id(): string
    {
        return 'openid-configuration';
    }

    public function name(): string
    {
        return 'OpenID Provider Configuration';
    }

    public function version(): string
    {
        return '1.0';
    }

    public function specification(): string
    {
        return 'https://openid.net/specs/openid-connect-discovery-1_0.html';
    }

    public function discover(): mixed
    {
        return $this->module->discover();
    }

    public function generate(): mixed
    {
        return $this->generator->generate();
    }

    public function validate(): mixed
    {
        return $this->module->validate($this->findResource());
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
        return $this->generator->supports($feature);
    }

    public function migrate(string $fromVersion): void
    {
    }

    private function findResource(): DiscoveredResource
    {
        foreach ($this->module->discover() as $resource) {
            if ($resource->id === $this->id()) {
                return $resource;
            }
        }

        throw new ModuleException('OAuth Discovery module has no "openid-configuration" resource.');
    }

    private function noDelegate(string $what): ModuleException
    {
        return new ModuleException(sprintf('OAuth Discovery module has no %s registered yet.', $what));
    }
}
