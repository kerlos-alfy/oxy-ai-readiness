<?php

/**
 * Fired when a Module is disabled.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Events;

use OxyAI\Contracts\ModuleInterface;

/**
 * Passed as the `do_action('oxy_ai_module_disabled', ...)` argument, per
 * docs/04-Folder-Structure.md's Events/ list (`ModuleDisabled.php`) and
 * docs/22-Plugin-SDK.md's Event Bus.
 */
final class ModuleDisabled
{
    public function __construct(public readonly ModuleInterface $module)
    {
    }
}
