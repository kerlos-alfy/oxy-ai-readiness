<?php

/**
 * Central handle to the plugin's Container and boot state.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Core;

use Closure;

/**
 * Instantiated once by Plugin and passed through Bootstrap/Kernel and
 * every Provider. Keeps boot-state tracking (has the plugin finished
 * its Bootstrap Sequence yet?) out of Container itself, which only
 * knows how to bind and resolve.
 */
final class Application
{
    private bool $booted = false;

    public function __construct(private readonly Container $container)
    {
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function isBooted(): bool
    {
        return $this->booted;
    }

    public function markBooted(): void
    {
        $this->booted = true;
    }

    public function make(string $id): mixed
    {
        return $this->container->make($id);
    }

    public function has(string $id): bool
    {
        return $this->container->has($id);
    }

    /**
     * @param Closure(): mixed $factory
     */
    public function bind(string $id, Closure $factory): void
    {
        $this->container->bind($id, $factory);
    }

    /**
     * @param Closure(): mixed $factory
     */
    public function singleton(string $id, Closure $factory): void
    {
        $this->container->singleton($id, $factory);
    }
}
