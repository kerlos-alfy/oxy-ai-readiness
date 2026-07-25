<?php

/**
 * Fired when a Module finishes booting.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Events;

use OxyAI\Contracts\ModuleInterface;

/**
 * Passed as the `do_action('oxy_ai_module_booted', ...)` argument.
 */
final class ModuleBooted
{
    public function __construct(public readonly ModuleInterface $module)
    {
    }
}
