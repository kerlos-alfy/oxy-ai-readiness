<?php

/**
 * Plugin Name:       Oxy AI Readiness
 * Description:       Prepare your WordPress website for AI Search, AI Agents & the Future of the Web.
 * Version:           0.1.0
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * License:           Proprietary
 * Text Domain:       oxy-ai-readiness
 * Domain Path:       /languages
 *
 * @package OxyAI
 */

declare(strict_types=1);

use OxyAI\Core\Plugin;

if (!defined('ABSPATH')) {
    exit;
}

define('OXY_AI_READINESS_VERSION', '0.1.0');
define('OXY_AI_READINESS_FILE', __FILE__);
define('OXY_AI_READINESS_MIN_PHP', '8.1');

if (version_compare(PHP_VERSION, OXY_AI_READINESS_MIN_PHP, '<')) {
    add_action('admin_notices', static function (): void {
        printf(
            '<div class="notice notice-error"><p>%s</p></div>',
            esc_html(
                sprintf(
                    /* translators: 1: required PHP version, 2: current PHP version */
                    __('Oxy AI Readiness requires PHP %1$s or higher. You are running PHP %2$s.', 'oxy-ai-readiness'),
                    OXY_AI_READINESS_MIN_PHP,
                    PHP_VERSION
                )
            )
        );
    });

    return;
}

require_once __DIR__ . '/vendor/autoload.php';

$oxyAiReadinessPlugin = new Plugin(OXY_AI_READINESS_FILE, OXY_AI_READINESS_VERSION);
$oxyAiReadinessPlugin->run();

register_activation_hook(OXY_AI_READINESS_FILE, [$oxyAiReadinessPlugin, 'activate']);
register_deactivation_hook(OXY_AI_READINESS_FILE, [$oxyAiReadinessPlugin, 'deactivate']);
