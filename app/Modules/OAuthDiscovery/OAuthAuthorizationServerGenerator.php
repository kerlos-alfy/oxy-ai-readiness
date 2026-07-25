<?php

/**
 * Generates the `oauth-authorization-server` well-known document.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\OAuthDiscovery;

use OxyAI\Contracts\GeneratorInterface;

/**
 * Per docs/05-Modules.md's OAuth Discovery module ("Generate OAuth
 * Discovery Files: oauth-authorization-server"). Same honesty
 * constraint as `OpenIdConfigurationGenerator`: RFC 8414 requires
 * `authorization_endpoint`/`token_endpoint` as mandatory fields, but
 * this site doesn't operate an OAuth 2.0 Authorization Server, so
 * those aren't fabricated — only the real issuer identity and an
 * explicit "not configured" note are published. See DECISIONS.md.
 */
final class OAuthAuthorizationServerGenerator implements GeneratorInterface
{
    public function id(): string
    {
        return 'oauth-authorization-server';
    }

    public function resourceId(): string
    {
        return 'oauth-authorization-server';
    }

    public function supports(string $type): bool
    {
        return $type === 'oauth-authorization-server';
    }

    public function generate(): string
    {
        return (string) wp_json_encode([
            'issuer' => home_url('/'),
            'authorization_endpoint' => null,
            'token_endpoint' => null,
            'response_types_supported' => [],
            'note' => 'This site does not currently operate an OAuth 2.0 Authorization Server.',
        ], JSON_PRETTY_PRINT);
    }
}
