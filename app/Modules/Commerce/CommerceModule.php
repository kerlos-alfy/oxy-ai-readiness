<?php

/**
 * The Commerce module: reports the site's AI-commerce readiness.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Modules\Commerce;

use OxyAI\Contracts\DiscoveryInterface;
use OxyAI\Contracts\GeneratorInterface;
use OxyAI\Contracts\ModuleInterface;
use OxyAI\Contracts\ValidatorInterface;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;

/**
 * Per docs/05-Modules.md's Commerce module — whose own stated Purpose
 * is "Future AI Commerce" (x402, Machine Payments, AI Checkout, Agent
 * Purchases, AI Transactions) — and `.project/09-Canonical-Architecture.md`'s
 * ownership table, which lists Commerce among the modules owning no
 * Standard. None of those payment/checkout capabilities exist in
 * WordPress core or this plugin; building fake payment infrastructure
 * would be both fabricated capability data (CLAUDE.md) and a genuine
 * security liability to pretend into existence. The one real,
 * currently-checkable fact this module can honestly report is whether
 * WooCommerce — the actual, real commerce platform most WordPress
 * sites would use — is active, via PHP's own `class_exists()` (no
 * WordPress runtime call, no stub needed), plus an honest declaration
 * that every AI-commerce capability itself is not yet supported. See
 * DECISIONS.md.
 */
final class CommerceModule implements ModuleInterface, DiscoveryInterface, ValidatorInterface, GeneratorInterface
{
    public function id(): string
    {
        return 'commerce';
    }

    public function name(): string
    {
        return 'Commerce';
    }

    public function version(): string
    {
        return '0.1.0';
    }

    public function description(): string
    {
        return "Reports the site's AI-commerce readiness.";
    }

    public function author(): string
    {
        return 'Oxy AI Readiness';
    }

    public function register(): void
    {
    }

    public function boot(): void
    {
    }

    public function init(): void
    {
    }

    public function assets(): array
    {
        return [];
    }

    public function routes(): array
    {
        return [];
    }

    public function settings(): array
    {
        return [];
    }

    public function permissions(): array
    {
        return [];
    }

    public function audit(): array
    {
        return [];
    }

    public function shutdown(): void
    {
    }

    public function discover(): array
    {
        return [
            new DiscoveredResource(
                id: 'commerce-status',
                type: 'commerce-status',
                location: '/.well-known/oxy-commerce-status',
                status: 'active',
                version: $this->version(),
                module: $this->id(),
                health: 'healthy',
                dependencies: [],
                source: 'commerce',
                lastChecked: gmdate('c')
            ),
        ];
    }

    /**
     * Real schema check: valid JSON with the two fields this module
     * always populates.
     */
    public function validate(DiscoveredResource $resource): ValidationResult
    {
        $start = microtime(true);
        $decoded = json_decode($this->generate(), true);
        $valid = is_array($decoded) && isset($decoded['woocommerce_active'], $decoded['supports']);
        $message = $valid
            ? 'Commerce status declaration is well-formed.'
            : 'Commerce status declaration is malformed.';

        return new ValidationResult(
            resourceId: $resource->id,
            validator: $this->id(),
            status: $valid ? ValidationStatus::Pass : ValidationStatus::Fail,
            message: $message,
            executionTimeMs: (microtime(true) - $start) * 1000
        );
    }

    public function resourceId(): string
    {
        return 'commerce-status';
    }

    public function supports(string $type): bool
    {
        return $type === 'commerce-status';
    }

    public function generate(): string
    {
        return (string) wp_json_encode([
            'woocommerce_active' => class_exists('WooCommerce'),
            'supports' => [
                'x402' => false,
                'machine_payments' => false,
                'ai_checkout' => false,
                'agent_purchases' => false,
            ],
        ], JSON_PRETTY_PRINT);
    }
}
