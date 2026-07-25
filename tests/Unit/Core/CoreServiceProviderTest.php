<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Core;

use OxyAI\Core\Application;
use OxyAI\Core\Config;
use OxyAI\Core\Container;
use OxyAI\Core\CoreServiceProvider;
use OxyAI\Core\ModuleRegistry;
use OxyAI\Core\StandardsRegistry;
use OxyAI\Services\AuditService;
use OxyAI\Services\AutoFixService;
use OxyAI\Services\DiscoveryService;
use OxyAI\Services\GenerationService;
use OxyAI\Services\MonitoringService;
use OxyAI\Services\RecommendationService;
use OxyAI\Services\ReportService;
use OxyAI\Services\ScoringService;
use OxyAI\Services\ValidationService;
use OxyAI\Tests\Unit\TestCase;

final class CoreServiceProviderTest extends TestCase
{
    public function test_register_binds_module_and_standards_registries_as_singletons(): void
    {
        $app = new Application(new Container());
        $provider = new CoreServiceProvider($app);

        $provider->register();

        self::assertInstanceOf(ModuleRegistry::class, $app->make(ModuleRegistry::class));
        self::assertSame($app->make(ModuleRegistry::class), $app->make(ModuleRegistry::class));

        self::assertInstanceOf(StandardsRegistry::class, $app->make(StandardsRegistry::class));
        self::assertSame($app->make(StandardsRegistry::class), $app->make(StandardsRegistry::class));
    }

    public function test_register_binds_discovery_service_as_a_singleton(): void
    {
        $app = new Application(new Container());
        $provider = new CoreServiceProvider($app);

        $provider->register();

        self::assertInstanceOf(DiscoveryService::class, $app->make(DiscoveryService::class));
        self::assertSame($app->make(DiscoveryService::class), $app->make(DiscoveryService::class));
    }

    public function test_register_binds_validation_service_as_a_singleton(): void
    {
        $app = new Application(new Container());
        $provider = new CoreServiceProvider($app);

        $provider->register();

        self::assertInstanceOf(ValidationService::class, $app->make(ValidationService::class));
        self::assertSame($app->make(ValidationService::class), $app->make(ValidationService::class));
    }

    public function test_register_binds_generation_service_as_a_singleton_using_config_for_its_storage_path(): void
    {
        $app = new Application(new Container());
        $app->singleton(Config::class, static fn (): Config => new Config('0.1.0', '/plugin.php'));

        $provider = new CoreServiceProvider($app);
        $provider->register();

        self::assertInstanceOf(GenerationService::class, $app->make(GenerationService::class));
        self::assertSame($app->make(GenerationService::class), $app->make(GenerationService::class));
    }

    public function test_register_binds_scoring_service_as_a_singleton(): void
    {
        $app = new Application(new Container());
        $provider = new CoreServiceProvider($app);

        $provider->register();

        self::assertInstanceOf(ScoringService::class, $app->make(ScoringService::class));
        self::assertSame($app->make(ScoringService::class), $app->make(ScoringService::class));
    }

    public function test_register_binds_audit_service_as_a_singleton(): void
    {
        $app = new Application(new Container());
        $provider = new CoreServiceProvider($app);

        $provider->register();

        self::assertInstanceOf(AuditService::class, $app->make(AuditService::class));
        self::assertSame($app->make(AuditService::class), $app->make(AuditService::class));
    }

    public function test_register_binds_recommendation_service_as_a_singleton(): void
    {
        $app = new Application(new Container());
        $app->singleton(Config::class, static fn (): Config => new Config('0.1.0', '/plugin.php'));

        $provider = new CoreServiceProvider($app);
        $provider->register();

        self::assertInstanceOf(RecommendationService::class, $app->make(RecommendationService::class));
        self::assertSame($app->make(RecommendationService::class), $app->make(RecommendationService::class));
    }

    public function test_register_binds_auto_fix_service_as_a_singleton(): void
    {
        $app = new Application(new Container());
        $app->singleton(Config::class, static fn (): Config => new Config('0.1.0', '/plugin.php'));

        $provider = new CoreServiceProvider($app);
        $provider->register();

        self::assertInstanceOf(AutoFixService::class, $app->make(AutoFixService::class));
        self::assertSame($app->make(AutoFixService::class), $app->make(AutoFixService::class));
    }

    public function test_register_binds_monitoring_service_as_a_singleton(): void
    {
        $app = new Application(new Container());
        $app->singleton(Config::class, static fn (): Config => new Config('0.1.0', '/plugin.php'));

        $provider = new CoreServiceProvider($app);
        $provider->register();

        self::assertInstanceOf(MonitoringService::class, $app->make(MonitoringService::class));
        self::assertSame($app->make(MonitoringService::class), $app->make(MonitoringService::class));
    }

    public function test_register_binds_report_service_as_a_singleton(): void
    {
        $app = new Application(new Container());
        $app->singleton(Config::class, static fn (): Config => new Config('0.1.0', '/plugin.php'));

        $provider = new CoreServiceProvider($app);
        $provider->register();

        self::assertInstanceOf(ReportService::class, $app->make(ReportService::class));
        self::assertSame($app->make(ReportService::class), $app->make(ReportService::class));
    }
}
