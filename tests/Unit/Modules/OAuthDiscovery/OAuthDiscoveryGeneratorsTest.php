<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\OAuthDiscovery;

use Brain\Monkey\Functions;
use OxyAI\Modules\OAuthDiscovery\OAuthAuthorizationServerGenerator;
use OxyAI\Modules\OAuthDiscovery\OAuthProtectedResourceGenerator;
use OxyAI\Modules\OAuthDiscovery\OpenIdConfigurationGenerator;
use OxyAI\Tests\Unit\TestCase;

final class OAuthDiscoveryGeneratorsTest extends TestCase
{
    public function test_open_id_configuration_generator_identity(): void
    {
        $generator = new OpenIdConfigurationGenerator();

        self::assertSame('openid-configuration', $generator->id());
        self::assertSame('openid-configuration', $generator->resourceId());
        self::assertTrue($generator->supports('openid-configuration'));
        self::assertFalse($generator->supports('oauth-authorization-server'));
    }

    public function test_open_id_configuration_generate_publishes_the_real_issuer_and_no_fake_endpoints(): void
    {
        Functions\expect('home_url')->once()->with('/')->andReturn('https://example.test/');

        $decoded = json_decode((new OpenIdConfigurationGenerator())->generate(), true);

        self::assertSame('https://example.test/', $decoded['issuer']);
        self::assertNull($decoded['authorization_endpoint']);
        self::assertNull($decoded['token_endpoint']);
        self::assertNull($decoded['jwks_uri']);
        self::assertSame([], $decoded['response_types_supported']);
    }

    public function test_authorization_server_generator_identity(): void
    {
        $generator = new OAuthAuthorizationServerGenerator();

        self::assertSame('oauth-authorization-server', $generator->id());
        self::assertSame('oauth-authorization-server', $generator->resourceId());
        self::assertTrue($generator->supports('oauth-authorization-server'));
        self::assertFalse($generator->supports('openid-configuration'));
    }

    public function test_authorization_server_generate_publishes_the_real_issuer_and_no_fake_endpoints(): void
    {
        Functions\expect('home_url')->once()->with('/')->andReturn('https://example.test/');

        $decoded = json_decode((new OAuthAuthorizationServerGenerator())->generate(), true);

        self::assertSame('https://example.test/', $decoded['issuer']);
        self::assertNull($decoded['authorization_endpoint']);
        self::assertNull($decoded['token_endpoint']);
    }

    public function test_protected_resource_generator_identity(): void
    {
        $generator = new OAuthProtectedResourceGenerator();

        self::assertSame('oauth-protected-resource', $generator->id());
        self::assertSame('oauth-protected-resource', $generator->resourceId());
        self::assertTrue($generator->supports('oauth-protected-resource'));
        self::assertFalse($generator->supports('openid-configuration'));
    }

    public function test_protected_resource_generate_publishes_the_real_rest_namespace(): void
    {
        Functions\expect('rest_url')->once()->with('oxy-ai/v1')->andReturn('https://example.test/wp-json/oxy-ai/v1');

        $decoded = json_decode((new OAuthProtectedResourceGenerator())->generate(), true);

        self::assertSame('https://example.test/wp-json/oxy-ai/v1', $decoded['resource']);
        self::assertSame([], $decoded['authorization_servers']);
        self::assertSame([], $decoded['bearer_methods_supported']);
        self::assertSame([], $decoded['scopes_supported']);
    }
}
