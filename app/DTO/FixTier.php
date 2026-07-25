<?php

/**
 * How much user confirmation an Auto Fix requires before running.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\DTO;

/**
 * Per the Phase 10 roadmap row's own wording ("safe/confirmation/
 * developer fix tiers"), narrowed from docs/18-AutoFix-Engine.md's
 * fuller "FIX TYPES" list (Safe, Confirmation Required, Developer,
 * Experimental, Manual Only, Unsupported).
 */
enum FixTier: string
{
    case Safe = 'safe';
    case Confirmation = 'confirmation';
    case Developer = 'developer';
}
