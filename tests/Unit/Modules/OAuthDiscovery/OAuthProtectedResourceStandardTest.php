<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\OAuthDiscovery;

use Brain\Monkey\Functions;
use OxyAI\Exceptions\ModuleException;
use OxyAI\Modules\OAuthDiscovery\OAuthAuthorizationServerGenerator;
use OxyAI\Modules\OAuthDiscovery\OAuthDiscoveryModule;
use OxyAI\Modules\OAuthDiscovery\OAuthProtectedResourceGenerator;
use OxyAI\Modules\OAuthDiscovery\OAuthProtectedResourceStandard;
use OxyAI\Modules\OAuthDiscovery\OpenIdConfigurationGenerator;
use OxyAI\Tests\Unit\TestCase;

final class OAuthProtectedResourceStandardTest extends TestCase
{
    private function makeStandard(): OAuthProtectedResourceStandard
    {
        $generator = new OAuthProtectedResourceGenerator();
        $module = new OAuthDiscoveryModule(
            new OpenIdConfigurationGenerator(),
            new OAuthAuthorizationServerGenerator(),
            $generator
        );

        return new OAuthProtectedResourceStandard($module, $generator);
    }

    public function test_exposes_its_identity(): void
    {
        $standard = $this->makeStandard();

        self::assertSame('oauth-protected-resource', $standard->id());
        self::assertSame('OAuth 2.0 Protected Resource Metadata', $standard->name());
        self::assertNotSame('', $standard->specification());
    }

    public function test_validate_delegates_to_the_owning_module_for_its_own_resource(): void
    {
        Functions\when('rest_url')->justReturn('https://example.test/wp-json/oxy-ai/v1');

        $standard = $this->makeStandard();

        self::assertSame('pass', $standard->validate()->status->value);
    }

    public function test_supports_delegates_to_its_own_generator(): void
    {
        $standard = $this->makeStandard();

        self::assertTrue($standard->supports('oauth-protected-resource'));
        self::assertFalse($standard->supports('openid-configuration'));
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function unimplementedDelegateMethodProvider(): iterable
    {
        yield 'score' => ['score'];
        yield 'monitor' => ['monitor'];
        yield 'report' => ['report'];
    }

    /**
     * @dataProvider unimplementedDelegateMethodProvider
     */
    public function test_delegate_methods_without_an_engine_yet_throw(string $method): void
    {
        $standard = $this->makeStandard();

        $this->expectException(ModuleException::class);

        $standard->$method();
    }
}
