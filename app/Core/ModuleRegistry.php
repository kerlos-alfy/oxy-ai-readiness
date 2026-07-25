<?php

/**
 * Registry of installed Modules and their runtime state.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Core;

use OxyAI\Contracts\ModuleInterface;
use OxyAI\Events\ModuleBooted;
use OxyAI\Events\ModuleDisabled;
use OxyAI\Events\ModuleEnabled;
use OxyAI\Events\ModuleRegistered;
use OxyAI\Exceptions\ModuleException;

/**
 * Per docs/05-Modules.md's Module Lifecycle and docs/22-Plugin-SDK.md's
 * Module Lifecycle (Install → Register → Boot → Ready → Running →
 * Suspend → Resume → Update → Disable → Uninstall): this phase
 * implements register/boot/enable/disable/remove — the subset the
 * "Module & Standard SDK skeleton" phase's exit criterion needs ("A
 * module can be enabled/disabled at runtime without touching core;
 * events fire on lifecycle transitions"). Suspend/Resume/Update are
 * deferred until a real module exercises them.
 *
 * State is in-memory only: no `oxy_modules` table or Settings Manager
 * exists yet (later phase), so enabled/disabled state does not survive
 * past the current request. That is consistent with "at runtime" in
 * the exit criterion, not a shortfall of it.
 */
final class ModuleRegistry
{
    /** @var array<string, ModuleInterface> */
    private array $modules = [];

    /** @var array<string, bool> */
    private array $enabled = [];

    /** @var array<string, bool> */
    private array $booted = [];

    public function register(ModuleInterface $module): void
    {
        $id = $module->id();

        if (isset($this->modules[$id])) {
            throw new ModuleException(sprintf('Module "%s" is already registered.', $id));
        }

        $module->register();

        $this->modules[$id] = $module;
        $this->enabled[$id] = true;
        $this->booted[$id] = false;

        do_action('oxy_ai_module_registered', new ModuleRegistered($module));
    }

    public function boot(string $id): void
    {
        $module = $this->get($id);

        if ($this->booted[$id] || !$this->enabled[$id]) {
            return;
        }

        $module->boot();
        $module->init();

        $this->booted[$id] = true;

        do_action('oxy_ai_module_booted', new ModuleBooted($module));
    }

    public function bootAll(): void
    {
        foreach (array_keys($this->modules) as $id) {
            $this->boot($id);
        }
    }

    public function enable(string $id): void
    {
        $this->assertRegistered($id);

        if ($this->enabled[$id]) {
            return;
        }

        $this->enabled[$id] = true;

        do_action('oxy_ai_module_enabled', new ModuleEnabled($this->modules[$id]));
    }

    public function disable(string $id): void
    {
        $this->assertRegistered($id);

        if (!$this->enabled[$id]) {
            return;
        }

        $this->enabled[$id] = false;
        $this->booted[$id] = false;

        do_action('oxy_ai_module_disabled', new ModuleDisabled($this->modules[$id]));
    }

    /**
     * Shuts the module down and removes it from the registry — the
     * lifecycle's terminal "Uninstall" step. Disables first if needed,
     * so a removed module always sees its disable transition.
     */
    public function remove(string $id): void
    {
        $this->assertRegistered($id);

        if ($this->enabled[$id]) {
            $this->disable($id);
        }

        $this->modules[$id]->shutdown();

        unset($this->modules[$id], $this->enabled[$id], $this->booted[$id]);
    }

    public function isEnabled(string $id): bool
    {
        $this->assertRegistered($id);

        return $this->enabled[$id];
    }

    public function isBooted(string $id): bool
    {
        $this->assertRegistered($id);

        return $this->booted[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->modules[$id]);
    }

    public function get(string $id): ModuleInterface
    {
        $this->assertRegistered($id);

        return $this->modules[$id];
    }

    /** @return array<string, ModuleInterface> */
    public function all(): array
    {
        return $this->modules;
    }

    private function assertRegistered(string $id): void
    {
        if (!isset($this->modules[$id])) {
            throw new ModuleException(sprintf('Module "%s" is not registered.', $id));
        }
    }
}
