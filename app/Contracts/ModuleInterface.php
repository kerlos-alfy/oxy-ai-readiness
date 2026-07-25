<?php

/**
 * Contract every Module implements.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Contracts;

/**
 * Per docs/05-Modules.md ("Every module MUST implement: ModuleInterface")
 * and docs/22-Plugin-SDK.md's base-class method list. A Module is the
 * WordPress integration shell (UI, REST, settings, permissions,
 * lifecycle) per ADR-001 — never business/engine logic itself.
 *
 * assets()/routes()/settings()/permissions()/audit() return empty
 * arrays for any module that genuinely has none yet (the only
 * implementation this phase, ProbeModule, has none of these — no
 * asset pipeline, REST router, Settings Manager, or Audit Engine
 * exists yet to register into). Their element shape is refined once
 * the corresponding subsystem ships (REST in a later phase, etc.);
 * deliberately left as loosely-typed arrays rather than guessing that
 * shape now.
 */
interface ModuleInterface
{
    public function id(): string;

    public function name(): string;

    public function version(): string;

    public function description(): string;

    public function author(): string;

    public function register(): void;

    public function boot(): void;

    public function init(): void;

    /** @return array<int, mixed> */
    public function assets(): array;

    /** @return array<int, mixed> */
    public function routes(): array;

    /** @return array<int, mixed> */
    public function settings(): array;

    /** @return array<int, mixed> */
    public function permissions(): array;

    /** @return array<int, mixed> */
    public function audit(): array;

    public function shutdown(): void;
}
