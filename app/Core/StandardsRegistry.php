<?php

/**
 * Registry of registered AI Standards and their runtime state.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Core;

use OxyAI\Contracts\StandardInterface;
use OxyAI\Events\StandardDisabled;
use OxyAI\Events\StandardEnabled;
use OxyAI\Events\StandardRegistered;
use OxyAI\Exceptions\ModuleException;

/**
 * Bookkeeping only, per docs/23-AI-Standards-Layer.md's Standard
 * Registry section (Register/Enable/Disable/Status). Unlike
 * ModuleRegistry, a Standard has no boot()/init() of its own to call —
 * its discover()/generate()/validate()/score()/monitor()/report()
 * methods are invoked on demand by the corresponding future engine
 * (Discovery Engine calls discover(), etc.), not by this registry.
 *
 * State is in-memory only, same reasoning as ModuleRegistry: no
 * `oxy_standards` table or Settings Manager exists yet.
 */
final class StandardsRegistry
{
    /** @var array<string, StandardInterface> */
    private array $standards = [];

    /** @var array<string, bool> */
    private array $enabled = [];

    public function register(StandardInterface $standard): void
    {
        $id = $standard->id();

        if (isset($this->standards[$id])) {
            throw new ModuleException(sprintf('Standard "%s" is already registered.', $id));
        }

        $this->standards[$id] = $standard;
        $this->enabled[$id] = true;

        do_action('oxy_ai_standard_registered', new StandardRegistered($standard));
    }

    public function enable(string $id): void
    {
        $this->assertRegistered($id);

        if ($this->enabled[$id]) {
            return;
        }

        $this->enabled[$id] = true;

        do_action('oxy_ai_standard_enabled', new StandardEnabled($this->standards[$id]));
    }

    public function disable(string $id): void
    {
        $this->assertRegistered($id);

        if (!$this->enabled[$id]) {
            return;
        }

        $this->enabled[$id] = false;

        do_action('oxy_ai_standard_disabled', new StandardDisabled($this->standards[$id]));
    }

    public function isEnabled(string $id): bool
    {
        $this->assertRegistered($id);

        return $this->enabled[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->standards[$id]);
    }

    public function get(string $id): StandardInterface
    {
        $this->assertRegistered($id);

        return $this->standards[$id];
    }

    /** @return array<string, StandardInterface> */
    public function all(): array
    {
        return $this->standards;
    }

    private function assertRegistered(string $id): void
    {
        if (!isset($this->standards[$id])) {
            throw new ModuleException(sprintf('Standard "%s" is not registered.', $id));
        }
    }
}
