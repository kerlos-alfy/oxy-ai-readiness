<?php

/**
 * Registers the OAuth Discovery module into every engine it participates in.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\OAuthDiscovery;

use OxyAI\Core\ModuleRegistry;
use OxyAI\Core\StandardsRegistry;
use OxyAI\Providers\ServiceProvider;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\ValidationService;

/**
 * Unlike every other module's ServiceProvider, registers three
 * Generators (one per well-known document) and three Standards under
 * one Module and one Validator registration — see
 * `OAuthDiscoveryModule`'s own docblock for why.
 */
final class OAuthDiscoveryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $openIdConfiguration = new OpenIdConfigurationGenerator();
        $authorizationServer = new OAuthAuthorizationServerGenerator();
        $protectedResource = new OAuthProtectedResourceGenerator();

        $module = new OAuthDiscoveryModule($openIdConfiguration, $authorizationServer, $protectedResource);

        $this->app->make(ModuleRegistry::class)->register($module);
        $this->app->make(DiscoveryService::class)->registerProvider($module->id(), $module);
        $this->app->make(ValidationService::class)->registerValidator($module->id(), $module);

        $generation = $this->app->make(GenerationService::class);
        $generation->registerGenerator($openIdConfiguration->id(), $openIdConfiguration);
        $generation->registerGenerator($authorizationServer->id(), $authorizationServer);
        $generation->registerGenerator($protectedResource->id(), $protectedResource);

        $standards = $this->app->make(StandardsRegistry::class);
        $standards->register(new OpenIdConfigurationStandard($module, $openIdConfiguration));
        $standards->register(new OAuthAuthorizationServerStandard($module, $authorizationServer));
        $standards->register(new OAuthProtectedResourceStandard($module, $protectedResource));
    }

    public function boot(): void
    {
        $this->app->make(ModuleRegistry::class)->boot('oauth-discovery');
    }
}
