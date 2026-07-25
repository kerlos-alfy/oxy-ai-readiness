<?php

/**
 * The OAuth Discovery module: manages the OAuth/OIDC well-known documents.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\OAuthDiscovery;

use OxyAI\Contracts\DiscoveryInterface;
use OxyAI\Contracts\ModuleInterface;
use OxyAI\Contracts\ValidatorInterface;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;

/**
 * Per ADR-001/`.project/09-Canonical-Architecture.md`'s ownership
 * table, OAuth Discovery is the one module owning three Standards
 * (`openid-configuration`, `oauth-authorization-server`,
 * `oauth-protected-resource`) instead of one. It therefore does *not*
 * implement `GeneratorInterface` itself — one module can't be
 * registered as "the" generator for three different generator/resource
 * ids under `GenerationService`'s one-id-to-one-generator registry —
 * each file has its own small dedicated Generator class instead
 * (`OpenIdConfigurationGenerator`, `OAuthAuthorizationServerGenerator`,
 * `OAuthProtectedResourceGenerator`), all owned by this one Module and
 * constructor-injected here so `validate()` can dispatch to whichever
 * one a given Discovery Map entry corresponds to. See DECISIONS.md.
 */
final class OAuthDiscoveryModule implements ModuleInterface, DiscoveryInterface, ValidatorInterface
{
    public function __construct(
        private readonly OpenIdConfigurationGenerator $openIdConfiguration,
        private readonly OAuthAuthorizationServerGenerator $authorizationServer,
        private readonly OAuthProtectedResourceGenerator $protectedResource
    ) {
    }

    public function id(): string
    {
        return 'oauth-discovery';
    }

    public function name(): string
    {
        return 'OAuth Discovery';
    }

    public function version(): string
    {
        return '0.1.0';
    }

    public function description(): string
    {
        return 'Generates and validates the OAuth/OIDC well-known discovery documents.';
    }

    public function author(): string
    {
        return 'Oxy AI Readiness';
    }

    public function register(): void
    {
    }

    public function boot(): void
    {
    }

    public function init(): void
    {
    }

    public function assets(): array
    {
        return [];
    }

    public function routes(): array
    {
        return [];
    }

    public function settings(): array
    {
        return [];
    }

    public function permissions(): array
    {
        return [];
    }

    public function audit(): array
    {
        return [];
    }

    public function shutdown(): void
    {
    }

    public function discover(): array
    {
        return [
            new DiscoveredResource(
                id: 'openid-configuration',
                type: 'openid-configuration',
                location: '/.well-known/openid-configuration',
                status: 'active',
                version: $this->version(),
                module: $this->id(),
                health: 'healthy',
                dependencies: [],
                source: 'oauth-discovery',
                lastChecked: gmdate('c')
            ),
            new DiscoveredResource(
                id: 'oauth-authorization-server',
                type: 'oauth-authorization-server',
                location: '/.well-known/oauth-authorization-server',
                status: 'active',
                version: $this->version(),
                module: $this->id(),
                health: 'healthy',
                dependencies: [],
                source: 'oauth-discovery',
                lastChecked: gmdate('c')
            ),
            new DiscoveredResource(
                id: 'oauth-protected-resource',
                type: 'oauth-protected-resource',
                location: '/.well-known/oauth-protected-resource',
                status: 'active',
                version: $this->version(),
                module: $this->id(),
                health: 'healthy',
                dependencies: [],
                source: 'oauth-discovery',
                lastChecked: gmdate('c')
            ),
        ];
    }

    /**
     * Dispatches to whichever of the three generators corresponds to
     * the given resource, then checks a real, honest requirement: the
     * one field this module actually promises for that document (see
     * each generator's own docblock for why the rest of each spec's
     * required fields aren't populated).
     */
    public function validate(DiscoveredResource $resource): ValidationResult
    {
        $start = microtime(true);

        $generator = match ($resource->id) {
            'openid-configuration' => $this->openIdConfiguration,
            'oauth-authorization-server' => $this->authorizationServer,
            'oauth-protected-resource' => $this->protectedResource,
            default => null,
        };

        if ($generator === null) {
            return new ValidationResult(
                resourceId: $resource->id,
                validator: $this->id(),
                status: ValidationStatus::Skipped,
                message: 'Not an OAuth Discovery resource.',
                executionTimeMs: (microtime(true) - $start) * 1000
            );
        }

        $requiredField = $resource->id === 'oauth-protected-resource' ? 'resource' : 'issuer';
        $decoded = json_decode($generator->generate(), true);
        $present = is_array($decoded) && isset($decoded[$requiredField]) && $decoded[$requiredField] !== '';
        $status = $present ? ValidationStatus::Pass : ValidationStatus::Fail;

        return new ValidationResult(
            resourceId: $resource->id,
            validator: $this->id(),
            status: $status,
            message: $present
                ? sprintf('"%s" has a real "%s".', $resource->id, $requiredField)
                : sprintf('"%s" is missing its required "%s" field.', $resource->id, $requiredField),
            executionTimeMs: (microtime(true) - $start) * 1000
        );
    }
}
