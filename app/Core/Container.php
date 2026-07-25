<?php

/**
 * Minimal dependency-injection container.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Core;

use Closure;
use OutOfBoundsException;

/**
 * Binds string identifiers (typically fully-qualified class/interface
 * names) to factory closures and resolves them on demand — the
 * "Service Container" step of the Bootstrap Sequence in
 * docs/02-Architecture.md. Nothing is bound here except what this
 * phase's own wiring (Plugin) and tests need; later phases' Providers
 * populate it with real services.
 *
 * Factories are zero-argument by design: no bound service yet needs
 * the container to resolve its own dependencies. Auto-wiring can be
 * added once a real consumer needs it, rather than built speculatively.
 */
final class Container
{
    /** @var array<string, Closure(): mixed> */
    private array $bindings = [];

    /** @var array<string, mixed> */
    private array $instances = [];

    /** @var array<string, bool> */
    private array $shared = [];

    /**
     * @param Closure(): mixed $factory
     */
    public function bind(string $id, Closure $factory): void
    {
        $this->bindings[$id] = $factory;
        $this->shared[$id] = false;
        unset($this->instances[$id]);
    }

    /**
     * @param Closure(): mixed $factory
     */
    public function singleton(string $id, Closure $factory): void
    {
        $this->bindings[$id] = $factory;
        $this->shared[$id] = true;
        unset($this->instances[$id]);
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]);
    }

    public function make(string $id): mixed
    {
        if (array_key_exists($id, $this->instances)) {
            return $this->instances[$id];
        }

        if (!isset($this->bindings[$id])) {
            throw new OutOfBoundsException(
                sprintf('No binding registered for "%s".', $id)
            );
        }

        $value = ($this->bindings[$id])();

        if ($this->shared[$id]) {
            $this->instances[$id] = $value;
        }

        return $value;
    }
}
