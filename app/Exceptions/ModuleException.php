<?php

/**
 * Thrown for Module/Standard registry and lifecycle errors.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Exceptions;

use RuntimeException;

/**
 * Covers both ModuleRegistry/StandardsRegistry bookkeeping errors
 * (duplicate/unknown id) and a Standard's delegate methods being
 * invoked with nothing to delegate to yet (its owning Module has no
 * Generator/Validator/ScoreProvider/Monitor/Reporter registered until
 * the corresponding engine phase ships). One exception class per
 * docs/04-Folder-Structure.md's Exceptions/ list, which does not
 * document a separate StandardException.
 */
final class ModuleException extends RuntimeException
{
}
