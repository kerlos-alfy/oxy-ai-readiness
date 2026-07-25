<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\OAuthDiscovery;

use Brain\Monkey\Functions;
use OxyAI\Exceptions\ModuleException;
use OxyAI\Modules\OAuthDiscovery\OAuthAuthorizationServerGenerator;
use OxyAI\Modules\OAuthDiscovery\OAuthDiscoveryModule;
use OxyAI\Modules\OAuthDiscovery\OAuthProtectedResourceGenerator;
use OxyAI\Modules\OAuthDiscovery\OpenIdConfigurationGenerator;
use OxyAI\Modules\OAuthDiscovery\OpenIdConfigurationStandard;
use OxyAI\Tests\Unit\TestCase;

final class OpenIdConfigurationStandardTest extends TestCase
{
    private function makeStandard(): OpenIdConfigurationStandard
    {
        $generator = new OpenIdConfigurationGenerator();
        $module = new OAuthDiscoveryModule(
            $generator,
            new OAuthAuthorizationServerGenerator(),
            new OAuthProtectedResourceGenerator()
        );

        return new OpenIdConfigurationStandard($module, $generator);
    }

    public function test_exposes_its_identity(): void
    {
        $standard = $this->makeStandard();

        self::assertSame('openid-configuration', $standard->id());
        self::assertSame('OpenID Provider Configuration', $standard->name());
        self::assertNotSame('', $standard->specification());
    }

    public function test_generate_delegates_to_its_own_generator_and_discover_to_the_module(): void
    {
        Functions\when('home_url')->justReturn('https://example.test/');

        $generator = new OpenIdConfigurationGenerator();
        $module = new OAuthDiscoveryModule(
            $generator,
            new OAuthAuthorizationServerGenerator(),
            new OAuthProtectedResourceGenerator()
        );
        $standard = new OpenIdConfigurationStandard($module, $generator);

        self::assertSame($generator->generate(), $standard->generate());
        self::assertEquals($module->discover(), $standard->discover());
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

        self::assertTrue($standard->supports('openid-configuration'));
        self::assertFalse($standard->supports('oauth-authorization-server'));
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
