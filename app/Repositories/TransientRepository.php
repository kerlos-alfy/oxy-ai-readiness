<?php

/**
 * Repository wrapping the native WordPress transients API.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Repositories;

use InvalidArgumentException;
use OxyAI\Contracts\RepositoryInterface;

/**
 * Wraps get_transient()/set_transient()/delete_transient().
 *
 * This is the "Transient Cache" tier referenced in
 * docs/27-Performance-Spec.md's Cache Architecture — one of several cache
 * backends. Multi-tier orchestration (deciding whether to use the object
 * cache, transients, or the filesystem cache) belongs to a future
 * CacheService, not this repository; this class only provides the safe,
 * validated primitive for the transient tier specifically.
 *
 * Every key is namespaced with an `oxy_ai_` prefix. WordPress stores
 * transient keys in `wp_options.option_name` behind `_transient_` /
 * `_transient_timeout_` prefixes (max 191 chars in modern WordPress), so
 * the usable key length is validated defensively at a conservative limit.
 *
 * Note (WordPress quirk, not an Oxy limitation): `get_transient()` returns
 * `false` both when a transient does not exist AND when `false` was
 * explicitly stored as its value — WordPress core provides no way to
 * disambiguate the two. Callers that must distinguish "missing" from
 * "stored false" should avoid storing `false` as a transient value.
 */
final class TransientRepository implements RepositoryInterface
{
    private const PREFIX = 'oxy_ai_';
    private const MAX_KEY_LENGTH = 150;

    public function get(string $key): mixed
    {
        return get_transient($this->prefixedKey($key));
    }

    /**
     * @param string $key   Unprefixed transient key.
     * @param mixed  $value Value to store. Must be serializable.
     * @param int    $ttl   Seconds until expiration. 0 means "no
     *                      expiration" (persists until explicitly deleted
     *                      or evicted by WordPress), matching
     *                      set_transient()'s own semantics.
     */
    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        if ($ttl < 0) {
            throw new InvalidArgumentException('Transient TTL cannot be negative.');
        }

        return set_transient($this->prefixedKey($key), $value, $ttl);
    }

    public function delete(string $key): bool
    {
        return delete_transient($this->prefixedKey($key));
    }

    /**
     * Cache-aside helper: return the cached value if present, otherwise
     * compute it via $callback, store it, and return it.
     *
     * @param string   $key      Unprefixed transient key.
     * @param int      $ttl      Seconds until expiration.
     * @param callable $callback Invoked only on a cache miss.
     */
    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        $cached = $this->get($key);

        if ($cached !== false) {
            return $cached;
        }

        $value = $callback();

        $this->set($key, $value, $ttl);

        return $value;
    }

    private function prefixedKey(string $key): string
    {
        if ($key === '' || preg_match('/^[a-z][a-z0-9_.\-]*$/', $key) !== 1) {
            throw new InvalidArgumentException(
                sprintf('Invalid transient key "%s". Keys must be lowercase snake_case.', $key)
            );
        }

        $prefixed = self::PREFIX . $key;

        if (strlen($prefixed) > self::MAX_KEY_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Transient key "%s" exceeds the maximum length of %d characters.', $key, self::MAX_KEY_LENGTH)
            );
        }

        return $prefixed;
    }
}
