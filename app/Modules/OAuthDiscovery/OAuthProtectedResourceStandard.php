<?php

/**
 * The oauth-protected-resource AI Standard, owned by the OAuth
 * Discovery module.
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
 * "oauth-protected-resource" Standard (one of three — see
 * `OAuthDiscoveryModule`'s own docblock). The only one of the three
 * whose generator is fully spec-compliant (RFC 9728) rather than an
 * honest minimal placeholder.
 */
final class OAuthProtectedResourceStandard implements StandardInterface
{
    public function __construct(
        private readonly OAuthDiscoveryModule $module,
        private readonly OAuthProtectedResourceGenerator $generator
    ) {
    }

    public function id(): string
    {
        return 'oauth-protected-resource';
    }

    public function name(): string
    {
        return 'OAuth 2.0 Protected Resource Metadata';
    }

    public function version(): string
    {
        return '1.0';
    }

    public function specification(): string
    {
        return 'https://datatracker.ietf.org/doc/html/rfc9728';
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

        throw new ModuleException('OAuth Discovery module has no "oauth-protected-resource" resource.');
    }

    private function noDelegate(string $what): ModuleException
    {
        return new ModuleException(sprintf('OAuth Discovery module has no %s registered yet.', $what));
    }
}
