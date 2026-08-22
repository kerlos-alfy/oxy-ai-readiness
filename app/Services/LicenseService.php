<?php

/**
 * License storage, validation, and daily re-checks.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Services;

use WP_Error;

final class LicenseService
{
    public const OPTION_KEY = 'license_key';
    public const STATE_TRANSIENT = 'oxy_ai_license_state';
    public const CRON_HOOK = 'oxy_ai_license_recheck';
    public const STATE_TTL = 86400;

    private const CIPHER = 'aes-256-gcm';
    private const FORMAT_PREFIX = 'v1:';

    public function register(): void
    {
        add_action(self::CRON_HOOK, [$this, 'recheck']);
        add_action('admin_notices', [$this, 'renderInvalidNotice']);
        $this->activateCron();
    }

    public function activateCron(): void
    {
        if (wp_next_scheduled(self::CRON_HOOK) === false) {
            wp_schedule_event(time() + self::STATE_TTL, 'daily', self::CRON_HOOK);
        }
    }

    public function deactivateCron(): void
    {
        wp_clear_scheduled_hook(self::CRON_HOOK);
    }

    /**
     * @return array{valid: bool, tier: string|null, sites_used: int, sites_max: int, expires_at: string|null, trial: bool, checked_at: string|null, network_error: bool}
     */
    public function state(): array
    {
        $state = get_transient(self::STATE_TRANSIENT);
        if (is_array($state)) {
            return $this->normalizeState($state);
        }

        return $this->emptyState();
    }

    public function hasKey(): bool
    {
        return $this->key() !== null;
    }

    public function maskedKey(): ?string
    {
        $key = $this->key();
        if ($key === null) {
            return null;
        }

        $suffix = strlen($key) > 4 ? substr($key, -4) : $key;
        return str_repeat('•', 12) . $suffix;
    }

    public function key(): ?string
    {
        $encrypted = get_option(self::OPTION_KEY, '');
        if (!is_string($encrypted) || $encrypted === '') {
            return null;
        }

        return $this->decrypt($encrypted);
    }

    /**
     * @return array{valid: bool, tier: string|null, sites_used: int, sites_max: int, expires_at: string|null, trial: bool, checked_at: string|null, network_error: bool}|WP_Error
     */
    public function activate(string $key): array|WP_Error
    {
        $key = trim($key);
        if ($key === '') {
            return new WP_Error('oxy_ai_license_empty', __('Enter a license key.', 'oxy-ai-readiness'), ['status' => 400]);
        }

        $encrypted = $this->encrypt($key);
        if ($encrypted instanceof WP_Error) {
            return $encrypted;
        }

        update_option(self::OPTION_KEY, $encrypted, false);
        return $this->validate(true);
    }

    public function clear(): void
    {
        delete_option(self::OPTION_KEY);
        delete_transient(self::STATE_TRANSIENT);
    }

    /**
     * @return array{valid: bool, tier: string|null, sites_used: int, sites_max: int, expires_at: string|null, trial: bool, checked_at: string|null, network_error: bool}|WP_Error
     */
    public function validate(bool $force = false): array|WP_Error
    {
        if (!$force) {
            $cached = get_transient(self::STATE_TRANSIENT);
            if (is_array($cached)) {
                return $this->normalizeState($cached);
            }
        }

        $key = $this->key();
        if ($key === null) {
            return $this->emptyState();
        }

        $url = add_query_arg(['license' => $key, 'site' => site_url()], $this->manifestUrl());
        $response = wp_remote_get($url, [
            'timeout' => 15,
            'redirection' => 3,
            'sslverify' => true,
        ]);

        if (is_wp_error($response)) {
            return $this->preserveOnNetworkError();
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        if ($code === 403) {
            $state = $this->emptyState();
            $state['checked_at'] = gmdate('c');
            set_transient(self::STATE_TRANSIENT, $state, self::STATE_TTL);
            return $state;
        }

        if ($code !== 200) {
            return $this->preserveOnNetworkError();
        }

        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        if (!is_array($body)) {
            return new WP_Error(
                'oxy_ai_license_malformed',
                __('The license response was malformed.', 'oxy-ai-readiness'),
                ['status' => 502]
            );
        }

        $payload = isset($body['license']) && is_array($body['license']) ? $body['license'] : $body;
        $state = $this->stateFromPayload($payload);
        if ($state instanceof WP_Error) {
            return $state;
        }

        set_transient(self::STATE_TRANSIENT, $state, self::STATE_TTL);
        return $state;
    }

    public function recheck(): void
    {
        $this->validate(true);
    }

    public function renderInvalidNotice(): void
    {
        if (!$this->hasKey()) {
            return;
        }

        $state = $this->state();
        if ($state['valid'] || $state['network_error'] || $state['checked_at'] === null) {
            return;
        }

        printf(
            '<div class="notice notice-error"><p>%s</p></div>',
            esc_html__('Oxy AI Readiness license is invalid. Updates are unavailable until the license is activated.', 'oxy-ai-readiness')
        );
    }

    public function encryptForStorage(string $plaintext): string|WP_Error
    {
        return $this->encrypt($plaintext);
    }

    public function decryptFromStorage(string $ciphertext): ?string
    {
        return $this->decrypt($ciphertext);
    }

    private function encrypt(string $plaintext): string|WP_Error
    {
        if (!function_exists('openssl_encrypt')) {
            return new WP_Error(
                'oxy_ai_license_crypto',
                __('OpenSSL is required to encrypt the license key.', 'oxy-ai-readiness'),
                ['status' => 500]
            );
        }

        $iv = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $this->encryptionKey(),
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        if (!is_string($ciphertext)) {
            return new WP_Error(
                'oxy_ai_license_crypto',
                __('The license key could not be encrypted.', 'oxy-ai-readiness'),
                ['status' => 500]
            );
        }

        return self::FORMAT_PREFIX . base64_encode($iv . $tag . $ciphertext);
    }

    private function decrypt(string $stored): ?string
    {
        if (!str_starts_with($stored, self::FORMAT_PREFIX) || !function_exists('openssl_decrypt')) {
            return null;
        }

        $decoded = base64_decode(substr($stored, strlen(self::FORMAT_PREFIX)), true);
        if (!is_string($decoded) || strlen($decoded) < 29) {
            return null;
        }

        $iv = substr($decoded, 0, 12);
        $tag = substr($decoded, 12, 16);
        $ciphertext = substr($decoded, 28);
        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $this->encryptionKey(),
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        return is_string($plaintext) ? $plaintext : null;
    }

    private function encryptionKey(): string
    {
        $auth = defined('AUTH_KEY') ? (string) AUTH_KEY : '';
        $secure = defined('SECURE_AUTH_KEY') ? (string) SECURE_AUTH_KEY : '';
        return hash('sha256', $auth . '|' . $secure . '|oxy-ai-license-v1', true);
    }

    private function manifestUrl(): string
    {
        return defined('OXY_AI_UPDATE_MANIFEST_URL')
            ? (string) OXY_AI_UPDATE_MANIFEST_URL
            : 'https://updates.oxyadvertising.com/oxy-ai-readiness/manifest.json';
    }

    /**
     * @param array<mixed> $payload
     * @return array{valid: bool, tier: string|null, sites_used: int, sites_max: int, expires_at: string|null, trial: bool, checked_at: string|null, network_error: bool}|WP_Error
     */
    private function stateFromPayload(array $payload): array|WP_Error
    {
        $tier = $payload['tier'] ?? null;
        $valid = (bool) ($payload['valid'] ?? true);
        if ($valid && (!is_string($tier) || !in_array($tier, ['personal', 'agency', 'unlimited'], true))) {
            return new WP_Error(
                'oxy_ai_license_tier',
                __('The license response contained an unknown tier.', 'oxy-ai-readiness'),
                ['status' => 502]
            );
        }

        $expires = $payload['expires_at'] ?? null;
        return [
            'valid' => $valid,
            'tier' => $valid && is_string($tier) ? $tier : null,
            'sites_used' => max(0, (int) ($payload['sites_used'] ?? 0)),
            'sites_max' => max(0, (int) ($payload['sites_max'] ?? 0)),
            'expires_at' => is_string($expires) ? $expires : null,
            'trial' => (bool) ($payload['trial'] ?? false),
            'checked_at' => gmdate('c'),
            'network_error' => false,
        ];
    }

    /**
     * @return array{valid: bool, tier: string|null, sites_used: int, sites_max: int, expires_at: string|null, trial: bool, checked_at: string|null, network_error: bool}
     */
    private function preserveOnNetworkError(): array
    {
        $existing = get_transient(self::STATE_TRANSIENT);
        $state = is_array($existing) ? $this->normalizeState($existing) : $this->emptyState();
        $state['network_error'] = true;
        set_transient(self::STATE_TRANSIENT, $state, self::STATE_TTL);
        return $state;
    }

    /**
     * @param array<mixed> $state
     * @return array{valid: bool, tier: string|null, sites_used: int, sites_max: int, expires_at: string|null, trial: bool, checked_at: string|null, network_error: bool}
     */
    private function normalizeState(array $state): array
    {
        $tier = $state['tier'] ?? null;
        $expires = $state['expires_at'] ?? null;
        $checked = $state['checked_at'] ?? null;

        return [
            'valid' => (bool) ($state['valid'] ?? false),
            'tier' => is_string($tier) ? $tier : null,
            'sites_used' => max(0, (int) ($state['sites_used'] ?? 0)),
            'sites_max' => max(0, (int) ($state['sites_max'] ?? 0)),
            'expires_at' => is_string($expires) ? $expires : null,
            'trial' => (bool) ($state['trial'] ?? false),
            'checked_at' => is_string($checked) ? $checked : null,
            'network_error' => (bool) ($state['network_error'] ?? false),
        ];
    }

    /**
     * @return array{valid: bool, tier: string|null, sites_used: int, sites_max: int, expires_at: string|null, trial: bool, checked_at: string|null, network_error: bool}
     */
    private function emptyState(): array
    {
        return [
            'valid' => false,
            'tier' => null,
            'sites_used' => 0,
            'sites_max' => 0,
            'expires_at' => null,
            'trial' => false,
            'checked_at' => null,
            'network_error' => false,
        ];
    }
}
