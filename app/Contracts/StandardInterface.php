<?php

/**
 * Contract every AI Standard implements.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Contracts;

use OxyAI\Exceptions\ModuleException;

/**
 * Per docs/23-AI-Standards-Layer.md and ADR-001: a Standard is owned by
 * exactly one Module and lives in that Module's folder. Its own unique
 * responsibility is external-specification metadata
 * (specification()/version()/supports()/migrate()); discover()
 * through report() delegate to the owning Module's already-registered
 * Generator/Validator/ScoreProvider/Monitor/Reporter — a Standard never
 * reimplements that logic itself.
 *
 * Discovery/Validation/Generation/Scoring/Monitoring/Reporting engines
 * don't exist yet (later phases), so no Module has any of those
 * registered yet. Implementations this phase (ProbeStandard) must
 * throw ModuleException from these six methods rather than fabricate
 * a result — honestly reporting "nothing to delegate to" instead of
 * faking success. Return types are deliberately `mixed`: their real
 * shape is defined by the DTOs (docs/04-Folder-Structure.md's DTO/
 * list) the owning engines introduce.
 */
interface StandardInterface
{
    public function id(): string;

    public function name(): string;

    public function version(): string;

    public function specification(): string;

    /**
     * @throws ModuleException When the owning Module has no Discovery
     *                          provider registered yet.
     */
    public function discover(): mixed;

    /**
     * @throws ModuleException When the owning Module has no Generator
     *                          registered yet.
     */
    public function generate(): mixed;

    /**
     * @throws ModuleException When the owning Module has no Validator
     *                          registered yet.
     */
    public function validate(): mixed;

    /**
     * @throws ModuleException When the owning Module has no
     *                          ScoreProvider registered yet.
     */
    public function score(): mixed;

    /**
     * @throws ModuleException When the owning Module has no Monitor
     *                          registered yet.
     */
    public function monitor(): mixed;

    /**
     * @throws ModuleException When the owning Module has no Reporter
     *                          registered yet.
     */
    public function report(): mixed;

    public function supports(string $feature): bool;

    public function migrate(string $fromVersion): void;
}
