<?php

/**
 * Fired when a Standard is enabled.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Events;

use OxyAI\Contracts\StandardInterface;

/**
 * Passed as the `do_action('oxy_ai_standard_enabled', ...)` argument,
 * per docs/23-AI-Standards-Layer.md's Events section (`StandardEnabled`).
 */
final class StandardEnabled
{
    public function __construct(public readonly StandardInterface $standard)
    {
    }
}
