<?php

/**
 * Repository wrapping the native WordPress options API.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Repositories;

use InvalidArgumentException;
use OxyAI\Contracts\RepositoryInterface;

/**
 * Wraps get_option()/update_option()/delete_option() for small,
 * plugin-lifecycle-scoped values (e.g. installed version, activation
 * timestamp, install UUID).
 *
 * Per docs/25-Database-Schema.md, operational and historical data
 * (audits, discovery results, logs, etc.) belongs in dedicated `oxy_*`
 * tables, not wp_options — this repository exists for the narrower,
 * legitimate use of wp_options that WordPress itself expects plugins to
 * use (small config flags), never as a general-purpose datastore.
 *
 * Every key is namespaced with an `oxy_ai_` prefix to avoid collisions
 * with other plugins, and autoload defaults to false per
 * docs/27-Performance-Spec.md ("Avoid autoloading operational data").
 */
final class OptionsRepository implements RepositoryInterface
{
    private const PREFIX = 'oxy_ai_';

    /**
     * @param string $key Unprefixed option key. Must be a non-empty
     *                     lowercase-snake-case identifier.
     * @param mixed  $default Value returned when the option does not exist.
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return get_option($this->prefixedKey($key), $default);
    }

    /**
     * @param string $key      Unprefixed option key.
     * @param mixed  $value    Value to store. Must be serializable.
     * @param bool   $autoload Whether WordPress should autoload this
     *                         option on every request. Defaults to false;
     *                         only pass true for values genuinely needed
     *                         on nearly every request.
     */
    public function set(string $key, mixed $value, bool $autoload = false): bool
    {
        return update_option($this->prefixedKey($key), $value, $autoload);
    }

    public function delete(string $key): bool
    {
        return delete_option($this->prefixedKey($key));
    }

    public function has(string $key): bool
    {
        $sentinel = "\0oxy_ai_missing\0";

        return $this->get($key, $sentinel) !== $sentinel;
    }

    private function prefixedKey(string $key): string
    {
        if ($key === '' || preg_match('/^[a-z][a-z0-9_]*$/', $key) !== 1) {
            throw new InvalidArgumentException(
                sprintf('Invalid option key "%s". Keys must be lowercase snake_case.', $key)
            );
        }

        return self::PREFIX . $key;
    }
}
