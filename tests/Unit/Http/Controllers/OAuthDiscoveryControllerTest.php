<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Http\Controllers;

use Brain\Monkey\Functions;
use OxyAI\Http\Controllers\OAuthDiscoveryController;
use OxyAI\Modules\OAuthDiscovery\OAuthAuthorizationServerGenerator;
use OxyAI\Modules\OAuthDiscovery\OAuthProtectedResourceGenerator;
use OxyAI\Modules\OAuthDiscovery\OpenIdConfigurationGenerator;
use OxyAI\Repositories\FileRepository;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\ValidationService;
use OxyAI\Tests\Unit\Support\InMemoryFilesystem;
use OxyAI\Tests\Unit\TestCase;
use WP_REST_Request;

final class OAuthDiscoveryControllerTest extends TestCase
{
    private function makeGenerationService(): GenerationService
    {
        $generation = new GenerationService(
            new ValidationService(),
            new DiscoveryService(),
            new FileRepository('/base', new InMemoryFilesystem())
        );

        $generation->registerGenerator('openid-configuration', new OpenIdConfigurationGenerator());
        $generation->registerGenerator('oauth-authorization-server', new OAuthAuthorizationServerGenerator());
        $generation->registerGenerator('oauth-protected-resource', new OAuthProtectedResourceGenerator());

        return $generation;
    }

    public function test_authorize_checks_the_manage_options_capability(): void
    {
        Functions\expect('current_user_can')->once()->with('manage_options')->andReturn(true);

        self::assertTrue((new OAuthDiscoveryController($this->makeGenerationService()))->authorize());
    }

    public function test_index_reports_all_three_documents_as_not_published_before_any_save(): void
    {
        $response = (new OAuthDiscoveryController($this->makeGenerationService()))->index(new WP_REST_Request());

        self::assertSame(200, $response->get_status());
        $data = $response->get_data()['data'];
        self::assertArrayHasKey('openid-configuration', $data);
        self::assertArrayHasKey('oauth-authorization-server', $data);
        self::assertArrayHasKey('oauth-protected-resource', $data);
        self::assertFalse($data['openid-configuration']['published']);
    }
}
