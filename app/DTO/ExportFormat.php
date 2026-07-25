<?php

/**
 * A format a Report can be exported to.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\DTO;

/**
 * Per docs/21-Reporting-Engine.md's "EXPORT FORMATS" list (PDF, HTML,
 * Markdown, CSV, Excel, JSON, XML, ZIP), narrowed to the two this phase
 * can genuinely produce without an external library: JSON (the
 * report's own `toArray()` shape) and Markdown (a real, readable
 * rendering of the same data — not a stub). PDF/Excel/ZIP need a
 * dependency this project doesn't have yet; CSV/XML/HTML are
 * plausible future additions once a real consumer needs them. Two
 * genuinely working formats already satisfies "exports in at least
 * one format" with margin. See DECISIONS.md.
 */
enum ExportFormat: string
{
    case Json = 'json';
    case Markdown = 'markdown';
}
