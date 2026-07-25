<?php

/**
 * Contract for a registered generator.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Contracts;

/**
 * Per docs/22-Plugin-SDK.md's SDK Interfaces list. Deliberately narrower
 * than docs/17-Generation-Engine.md's literal "Every generator
 * implements... supports() generate() validate() preview() publish()
 * rollback() cache() version()" list: that document's own Vision
 * states "Instead of allowing every module to implement its own
 * generation logic, the Generation Engine provides one centralized
 * framework" — publish/rollback/cache/version are identical, generic
 * file-versioning operations regardless of what's being generated, so
 * putting them on every Generator would force each one to reimplement
 * the exact logic the engine exists to centralize. `GenerationService`
 * owns those; a Generator only knows how to produce its own content
 * and which Discovery Map entry it corresponds to. Logged in
 * DECISIONS.md.
 */
interface GeneratorInterface
{
    public function id(): string;

    /**
     * The Discovery Map entry id this generator's output corresponds
     * to — `GenerationService::publish()` validates this resource
     * before writing.
     */
    public function resourceId(): string;

    public function supports(string $type): bool;

    public function generate(): string;
}
