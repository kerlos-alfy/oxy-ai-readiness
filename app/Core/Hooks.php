<?php

/**
 * Registrar wrapping WordPress's action/filter hook functions.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Core;

/**
 * Every hook the plugin registers goes through this class instead of
 * calling add_action()/add_filter() directly, so later phases'
 * Providers register hooks declaratively and can be inspected/tested
 * through one object (the "Register Hooks" bootstrap step in
 * docs/02-Architecture.md). Kernel uses it for the plugin's own
 * `plugins_loaded` entry point; no module hooks are registered yet
 * since no modules exist.
 */
final class Hooks
{
    /** @var array<int, array{hook: string, callback: callable, priority: int, args: int}> */
    private array $actions = [];

    /** @var array<int, array{hook: string, callback: callable, priority: int, args: int}> */
    private array $filters = [];

    public function addAction(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        add_action($hook, $callback, $priority, $acceptedArgs);

        $this->actions[] = [
            'hook' => $hook,
            'callback' => $callback,
            'priority' => $priority,
            'args' => $acceptedArgs,
        ];
    }

    public function addFilter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        add_filter($hook, $callback, $priority, $acceptedArgs);

        $this->filters[] = [
            'hook' => $hook,
            'callback' => $callback,
            'priority' => $priority,
            'args' => $acceptedArgs,
        ];
    }

    /**
     * @return array<int, array{hook: string, callback: callable, priority: int, args: int}>
     */
    public function registeredActions(): array
    {
        return $this->actions;
    }

    /**
     * @return array<int, array{hook: string, callback: callable, priority: int, args: int}>
     */
    public function registeredFilters(): array
    {
        return $this->filters;
    }
}
