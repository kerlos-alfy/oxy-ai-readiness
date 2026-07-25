<?php

/**
 * Fired when a Standard is disabled.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Events;

use OxyAI\Contracts\StandardInterface;

/**
 * Passed as the `do_action('oxy_ai_standard_disabled', ...)` argument,
 * per docs/23-AI-Standards-Layer.md's Events section (`StandardDisabled`).
 */
final class StandardDisabled
{
    public function __construct(public readonly StandardInterface $standard)
    {
    }
}
