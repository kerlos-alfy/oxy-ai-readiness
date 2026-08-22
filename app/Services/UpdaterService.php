<?php

/**
 * Self-hosted updater with fail-closed RSA signature verification.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Services;

use stdClass;
use WP_Error;

final class UpdaterService
{
    public const MANIFEST_TRANSIENT = 'oxy_ai_update_manifest';
    public const MANIFEST_TTL = 43200;
    public const LAST_CHECKED_OPTION = 'oxy_ai_update_last_checked';
    public const AUTO_UPDATE_OPTION = 'oxy_ai_auto_update';
    public const SECURITY_EVENTS_OPTION = 'oxy_ai_updater_security_events';

    private const PLUGIN_SLUG = 'oxy-ai-readiness';
    private const PUBLIC_KEY_RELATIVE = 'resources/updater/public-key.pem';

    public function __construct(private readonly LicenseService $license)
    {
    }

    public function register(): void
    {
        add_filter('pre_set_site_transient_update_plugins', [$this, 'filterUpdateTransient']);
        add_filter('plugins_api', [$this, 'filterPluginsApi'], 20, 3);
        add_filter('upgrader_pre_download', [$this, 'verifyBeforeDownload'], 10, 4);
        add_filter('auto_update_plugin', [$this, 'filterAutoUpdate'], 10, 2);
    }

    /**
     * @return array<string, mixed>|WP_Error|null
     */
    public function manifest(bool $force = false): array|WP_Error|null
    {
        if (!$force) {
            $cached = get_site_transient(self::MANIFEST_TRANSIENT);
            if (is_array($cached)) {
                return $cached;
            }
        } else {
            delete_site_transient(self::MANIFEST_TRANSIENT);
        }

        $url = $this->manifestUrl();
        $key = $this->license->key();
        if ($key !== null) {
            $url = add_query_arg(['license' => $key, 'site' => site_url()], $url);
        }

        $response = wp_remote_get($url, [
            'timeout' => 15,
            'redirection' => 3,
            'sslverify' => true,
        ]);

        update_option(self::LAST_CHECKED_OPTION, gmdate('c'), false);

        if (is_wp_error($response)) {
            return new WP_Error(
                'oxy_ai_update_network',
                __('The update manifest could not be reached.', 'oxy-ai-readiness'),
                ['status' => 503]
            );
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        if ($code === 403) {
            $this->license->validate(true);
            return new WP_Error(
                'oxy_ai_update_license_invalid',
                __('Updates are unavailable because the license is invalid.', 'oxy-ai-readiness'),
                ['status' => 403]
            );
        }

        if ($code !== 200) {
            return new WP_Error(
                'oxy_ai_update_http',
                sprintf(
                    /* translators: %d: HTTP response code. */
                    __('The update service returned HTTP %d.', 'oxy-ai-readiness'),
                    $code
                ),
                ['status' => 502]
            );
        }

        $parsed = $this->parseManifest((string) wp_remote_retrieve_body($response));
        if ($parsed instanceof WP_Error) {
            return $parsed;
        }

        $parsed['changelog'] = $this->fetchChangelog((string) $parsed['changelog_url']);
        set_site_transient(self::MANIFEST_TRANSIENT, $parsed, self::MANIFEST_TTL);

        return $parsed;
    }

    /**
     * @return array<string, mixed>|WP_Error
     */
    public function parseManifest(string $json): array|WP_Error
    {
        $manifest = json_decode($json, true);
        if (!is_array($manifest)) {
            return new WP_Error('oxy_ai_manifest_json', __('The update manifest is not valid JSON.', 'oxy-ai-readiness'));
        }

        $required = [
            'version',
            'download_url',
            'signature_url',
            'requires_php',
            'requires_wp',
            'tested_wp',
            'changelog_url',
            'released_at',
        ];

        foreach ($required as $field) {
            if (!isset($manifest[$field]) || !is_string($manifest[$field]) || trim($manifest[$field]) === '') {
                return new WP_Error(
                    'oxy_ai_manifest_schema',
                    sprintf(
                        /* translators: %s: manifest field name. */
                        __('The update manifest is missing a valid %s field.', 'oxy-ai-readiness'),
                        $field
                    )
                );
            }
        }

        foreach (['download_url', 'signature_url', 'changelog_url'] as $urlField) {
            if (wp_http_validate_url($manifest[$urlField]) === false) {
                return new WP_Error('oxy_ai_manifest_url', __('The update manifest contains an invalid URL.', 'oxy-ai-readiness'));
            }
        }

        return $manifest;
    }

    public function versionRelation(string $remoteVersion): int
    {
        return version_compare($remoteVersion, $this->currentVersion());
    }

    /**
     * WordPress calls this filter while it builds the update transient.
     * Merely loading the plugin/admin SPA performs no manifest request.
     */
    public function filterUpdateTransient(mixed $transient): mixed
    {
        if (!is_object($transient)) {
            return $transient;
        }

        $manifest = $this->manifest(false);
        if (!is_array($manifest) || $this->versionRelation((string) $manifest['version']) <= 0) {
            return $transient;
        }

        if (!isset($transient->response) || !is_array($transient->response)) {
            $transient->response = [];
        }

        $item = new stdClass();
        $item->slug = self::PLUGIN_SLUG;
        $item->plugin = $this->pluginBasename();
        $item->new_version = (string) $manifest['version'];
        $item->url = (string) $manifest['changelog_url'];
        $item->package = (string) $manifest['download_url'];
        $item->requires = (string) $manifest['requires_wp'];
        $item->requires_php = (string) $manifest['requires_php'];
        $item->tested = (string) $manifest['tested_wp'];
        $transient->response[$this->pluginBasename()] = $item;

        return $transient;
    }

    public function filterPluginsApi(mixed $result, string $action, object $args): mixed
    {
        if ($action !== 'plugin_information' || !isset($args->slug) || $args->slug !== self::PLUGIN_SLUG) {
            return $result;
        }

        $manifest = get_site_transient(self::MANIFEST_TRANSIENT);
        if (!is_array($manifest)) {
            return $result;
        }

        $info = new stdClass();
        $info->name = 'Oxy AI Readiness';
        $info->slug = self::PLUGIN_SLUG;
        $info->version = (string) ($manifest['version'] ?? $this->currentVersion());
        $info->requires = (string) ($manifest['requires_wp'] ?? '6.5');
        $info->requires_php = (string) ($manifest['requires_php'] ?? '8.1');
        $info->tested = (string) ($manifest['tested_wp'] ?? '');
        $info->last_updated = (string) ($manifest['released_at'] ?? '');
        $info->homepage = (string) ($manifest['changelog_url'] ?? '');
        $info->sections = [
            'description' => __('Signed updates for Oxy AI Readiness are delivered from the configured update service.', 'oxy-ai-readiness'),
            'changelog' => $this->changelogHtml((string) ($manifest['changelog'] ?? ''), (string) ($manifest['changelog_url'] ?? '')),
        ];

        return $info;
    }

    /**
     * Downloads the package and detached signature ourselves so WordPress
     * receives a local ZIP only after verification succeeds.
     *
     * @param array<string, mixed> $hookExtra
     */
    public function verifyBeforeDownload(mixed $reply, string $package, object $upgrader, array $hookExtra): mixed
    {
        if (($hookExtra['plugin'] ?? null) !== $this->pluginBasename()) {
            return $reply;
        }

        $manifest = get_site_transient(self::MANIFEST_TRANSIENT);
        if (!is_array($manifest)) {
            return $this->verificationFailure('manifest_missing', __('The cached update manifest is missing; the package cannot be verified.', 'oxy-ai-readiness'));
        }

        $expectedPackage = (string) ($manifest['download_url'] ?? '');
        $signatureUrl = (string) ($manifest['signature_url'] ?? '');
        if ($expectedPackage === '' || $signatureUrl === '' || !hash_equals($expectedPackage, $package)) {
            return $this->verificationFailure('package_mismatch', __('The update package does not match the signed manifest.', 'oxy-ai-readiness'));
        }

        if (!function_exists('download_url')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        $zipPath = download_url($package, 60);
        if (is_wp_error($zipPath)) {
            return $this->verificationFailure('package_download_failed', $zipPath->get_error_message());
        }

        $sigPath = download_url($signatureUrl, 30);
        if (is_wp_error($sigPath)) {
            @unlink($zipPath);
            return $this->verificationFailure('signature_missing', $sigPath->get_error_message());
        }

        $verified = $this->verifySignature($zipPath, $sigPath, $this->publicKeyPath());
        @unlink($sigPath);

        if (!$verified) {
            @unlink($zipPath);
            return $this->verificationFailure('signature_invalid', __('Update signature verification failed. Installation was refused.', 'oxy-ai-readiness'));
        }

        return $zipPath;
    }

    public function verifySignature(string $archivePath, string $signaturePath, ?string $publicKeyPath = null): bool
    {
        if (!function_exists('openssl_verify') || !is_file($archivePath) || !is_file($signaturePath)) {
            return false;
        }

        $keyPath = $publicKeyPath ?? $this->publicKeyPath();
        if (!is_file($keyPath)) {
            return false;
        }

        $archive = file_get_contents($archivePath);
        $signature = file_get_contents($signaturePath);
        $key = file_get_contents($keyPath);
        if (!is_string($archive) || !is_string($signature) || !is_string($key)) {
            return false;
        }

        $publicKey = openssl_pkey_get_public($key);
        if ($publicKey === false) {
            return false;
        }

        return openssl_verify($archive, $signature, $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }

    public function filterAutoUpdate(bool $update, object $item): bool
    {
        $plugin = $item->plugin ?? null;
        if (!is_string($plugin) || $plugin !== $this->pluginBasename()) {
            return $update;
        }

        return (bool) get_option(self::AUTO_UPDATE_OPTION, false);
    }

    public function setAutoUpdate(bool $enabled): void
    {
        update_option(self::AUTO_UPDATE_OPTION, $enabled, false);
    }

    /**
     * @return array<string, mixed>
     */
    public function status(): array
    {
        $manifest = get_site_transient(self::MANIFEST_TRANSIENT);
        $manifest = is_array($manifest) ? $manifest : null;
        $latest = $manifest !== null && isset($manifest['version']) ? (string) $manifest['version'] : null;
        $lastChecked = get_option(self::LAST_CHECKED_OPTION, null);

        return [
            'current_version' => $this->currentVersion(),
            'latest_version' => $latest,
            'update_available' => $latest !== null && $this->versionRelation($latest) > 0,
            'released_at' => $manifest !== null && is_string($manifest['released_at'] ?? null) ? $manifest['released_at'] : null,
            'changelog_url' => $manifest !== null && is_string($manifest['changelog_url'] ?? null) ? $manifest['changelog_url'] : null,
            'changelog' => $manifest !== null && is_string($manifest['changelog'] ?? null) ? $manifest['changelog'] : '',
            'last_checked' => is_string($lastChecked) ? $lastChecked : null,
            'auto_update' => (bool) get_option(self::AUTO_UPDATE_OPTION, false),
            'license' => $this->license->state(),
            'license_activated' => $this->license->hasKey(),
            'license_key_masked' => $this->license->maskedKey(),
            'security_events' => $this->securityEvents(),
            'manage_license_url' => 'https://oxyadvertising.com/account',
            'support_url' => 'https://oxyadvertising.com/contact',
            'update_url' => $this->updateUrl(),
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function securityEvents(): array
    {
        $events = get_option(self::SECURITY_EVENTS_OPTION, []);
        if (!is_array($events)) {
            return [];
        }

        $normalized = [];
        foreach ($events as $event) {
            if (!is_array($event)) {
                continue;
            }
            $code = $event['code'] ?? null;
            $message = $event['message'] ?? null;
            $at = $event['at'] ?? null;
            if (is_string($code) && is_string($message) && is_string($at)) {
                $normalized[] = ['code' => $code, 'message' => $message, 'at' => $at];
            }
        }

        return $normalized;
    }

    private function verificationFailure(string $code, string $message): WP_Error
    {
        $event = ['code' => $code, 'message' => $message, 'at' => gmdate('c')];
        $events = $this->securityEvents();
        array_unshift($events, $event);
        update_option(self::SECURITY_EVENTS_OPTION, array_slice($events, 0, 50), false);
        do_action('oxy_ai_updater_signature_verification_failed', $event);

        return new WP_Error('oxy_ai_' . $code, $message);
    }

    private function publicKeyPath(): string
    {
        return dirname(__DIR__, 2) . '/' . self::PUBLIC_KEY_RELATIVE;
    }

    private function currentVersion(): string
    {
        return defined('OXY_AI_READINESS_VERSION') ? (string) OXY_AI_READINESS_VERSION : '1.0.0-alpha.5';
    }

    private function pluginBasename(): string
    {
        $file = defined('OXY_AI_READINESS_FILE') ? (string) OXY_AI_READINESS_FILE : dirname(__DIR__, 2) . '/oxy-ai-readiness.php';

        return plugin_basename($file);
    }

    private function manifestUrl(): string
    {
        return defined('OXY_AI_UPDATE_MANIFEST_URL')
            ? (string) OXY_AI_UPDATE_MANIFEST_URL
            : 'https://updates.oxyadvertising.com/oxy-ai-readiness/manifest.json';
    }

    private function fetchChangelog(string $url): string
    {
        $response = wp_remote_get($url, ['timeout' => 10, 'redirection' => 3, 'sslverify' => true]);
        if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) !== 200) {
            return '';
        }

        return trim((string) wp_remote_retrieve_body($response));
    }

    private function changelogHtml(string $changelog, string $url): string
    {
        if ($changelog !== '') {
            return '<pre>' . esc_html($changelog) . '</pre>';
        }

        if ($url !== '') {
            return sprintf('<p><a href="%s" target="_blank" rel="noopener noreferrer">%s</a></p>', esc_url($url), esc_html__('View changelog', 'oxy-ai-readiness'));
        }

        return '<p>' . esc_html__('No changelog has been fetched yet.', 'oxy-ai-readiness') . '</p>';
    }

    private function updateUrl(): ?string
    {
        if (!current_user_can('update_plugins')) {
            return null;
        }

        $url = self_admin_url('update.php?action=upgrade-plugin&plugin=' . rawurlencode($this->pluginBasename()));

        return wp_nonce_url($url, 'upgrade-plugin_' . $this->pluginBasename());
    }
}
