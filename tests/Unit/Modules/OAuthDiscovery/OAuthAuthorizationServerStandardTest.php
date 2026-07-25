<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\OAuthDiscovery;

use Brain\Monkey\Functions;
use OxyAI\Exceptions\ModuleException;
use OxyAI\Modules\OAuthDiscovery\OAuthAuthorizationServerGenerator;
use OxyAI\Modules\OAuthDiscovery\OAuthAuthorizationServerStandard;
use OxyAI\Modules\OAuthDiscovery\OAuthDiscoveryModule;
use OxyAI\Modules\OAuthDiscovery\OAuthProtectedResourceGenerator;
use OxyAI\Modules\OAuthDiscovery\OpenIdConfigurationGenerator;
use OxyAI\Tests\Unit\TestCase;

final class OAuthAuthorizationServerStandardTest extends TestCase
{
    private function makeStandard(): OAuthAuthorizationServerStandard
    {
        $generator = new OAuthAuthorizationServerGenerator();
        $module = new OAuthDiscoveryModule(
            new OpenIdConfigurationGenerator(),
            $generator,
            new OAuthProtectedResourceGenerator()
        );

        return new OAuthAuthorizationServerStandard($module, $generator);
    }

    public function test_exposes_its_identity(): void
    {
        $standard = $this->makeStandard();

        self::assertSame('oauth-authorization-server', $standard->id());
        self::assertSame('OAuth 2.0 Authorization Server Metadata', $standard->name());
        self::assertNotSame('', $standard->specification());
    }

    public function test_validate_delegates_to_the_owning_module_for_its_own_resource(): void
    {
        Functions\when('home_url')->justReturn('https://example.test/');

        $standard = $this->makeStandard();

        self::assertSame('pass', $standard->validate()->status->value);
    }

    public function test_supports_delegates_to_its_own_generator(): void
    {
        $standard = $this->makeStandard();

        self::assertTrue($standard->supports('oauth-authorization-server'));
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
