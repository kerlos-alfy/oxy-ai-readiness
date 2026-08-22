<?php

/**
 * PHPUnit bootstrap for Oxy AI Readiness.
 *
 * @package OxyAI
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (!defined('DAY_IN_SECONDS')) {
    define('DAY_IN_SECONDS', 86400);
}

require_once __DIR__ . '/stubs/wp-core-stubs.php';
