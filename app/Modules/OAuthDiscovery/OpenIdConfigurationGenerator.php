<?php

/**
 * Generates the `openid-configuration` well-known document.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\OAuthDiscovery;

use OxyAI\Contracts\GeneratorInterface;

/**
 * Per docs/05-Modules.md's OAuth Discovery module ("Generate OAuth
 * Discovery Files: openid-configuration"). Neither WordPress core nor
 * this plugin implements an actual OpenID Provider (no `/oauth/*`
 * authorization or token endpoints exist) — OpenID Connect Discovery
 * 1.0 requires `authorization_endpoint`/`token_endpoint`/`jwks_uri` as
 * mandatory fields, and inventing URLs for infrastructure that doesn't
 * exist would be exactly the fabricated capability data CLAUDE.md
 * prohibits. This generator instead publishes a real, honest, minimal
 * document: the site's own real issuer identity and an explicit note
 * that no Provider is configured, rather than a fake spec-compliant
 * one. See DECISIONS.md.
 */
final class OpenIdConfigurationGenerator implements GeneratorInterface
{
    public function id(): string
    {
        return 'openid-configuration';
    }

    public function resourceId(): string
    {
        return 'openid-configuration';
    }

    public function supports(string $type): bool
    {
        return $type === 'openid-configuration';
    }

    public function generate(): string
    {
        return (string) wp_json_encode([
            'issuer' => home_url('/'),
            'authorization_endpoint' => null,
            'token_endpoint' => null,
            'jwks_uri' => null,
            'response_types_supported' => [],
            'note' => 'This site does not currently operate an OpenID Provider.',
        ], JSON_PRETTY_PRINT);
    }
}
