<?php

/**
 * Fires when the plugin is deleted from wp-admin.
 *
 * @package OxyAI
 */

declare(strict_types=1);

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// No `oxy_*` tables, migrations, or persistent options beyond the two
// set by Plugin::activate() exist yet. Real cleanup (dropping tables,
// removing options, clearing scheduled events) is added as later
// phases introduce that state — not invented ahead of it here.
