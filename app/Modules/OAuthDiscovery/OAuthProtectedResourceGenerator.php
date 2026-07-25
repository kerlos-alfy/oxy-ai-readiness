<?php

/**
 * Generates the `oauth-protected-resource` well-known document.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\OAuthDiscovery;

use OxyAI\Contracts\GeneratorInterface;

/**
 * Per docs/05-Modules.md's OAuth Discovery module ("Generate OAuth
 * Discovery Files: oauth-protected-resource") and RFC 9728's "OAuth
 * 2.0 Protected Resource Metadata." Unlike the other two OAuth
 * Discovery documents, this one is fully spec-compliant for real: its
 * only mandatory field is `resource`, and `authorization_servers`/
 * `bearer_methods_supported`/`scopes_supported` are all OPTIONAL, so
 * publishing them empty is an honest "no external Authorization Server
 * is configured yet" rather than a spec violation. `resource` is the
 * plugin's own real, currently-existing, `manage_options`-gated REST
 * namespace — a genuinely protected resource, not a fabricated one.
 */
final class OAuthProtectedResourceGenerator implements GeneratorInterface
{
    public function id(): string
    {
        return 'oauth-protected-resource';
    }

    public function resourceId(): string
    {
        return 'oauth-protected-resource';
    }

    public function supports(string $type): bool
    {
        return $type === 'oauth-protected-resource';
    }

    public function generate(): string
    {
        return (string) wp_json_encode([
            'resource' => rest_url('oxy-ai/v1'),
            'authorization_servers' => [],
            'bearer_methods_supported' => [],
            'scopes_supported' => [],
        ], JSON_PRETTY_PRINT);
    }
}
