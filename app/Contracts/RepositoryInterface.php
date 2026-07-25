<?php

/**
 * Marker interface for every Repository implementation.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Contracts;

/**
 * Repositories are the only classes permitted to talk to WordPress storage
 * (options, posts, transients, filesystem, users, custom tables) directly.
 *
 * Services must depend on a Repository through this interface rather than
 * instantiating a concrete repository or calling WordPress data-access
 * functions themselves — see the Repository Pattern in
 * docs/02-Architecture.md ("Service -> Repository -> WordPress", never
 * "Service -> WP_Query").
 *
 * This is intentionally a marker interface with no required methods: the
 * five foundation repositories (Options, Post, File, Transient, User) each
 * wrap a different native WordPress storage API and have no meaningfully
 * shared method signature. The interface still serves two purposes: (1) it
 * lets the Service Container tag and discover every repository
 * implementation, matching the tagging pattern shown in
 * docs/29-Developer-Guide.md's ServiceProvider example
 * (`$this->container->tag(Concrete::class, SomeInterface::class)`), and
 * (2) it lets calling code type-hint "any repository" where useful (e.g. in
 * generic logging/instrumentation wrappers) without depending on a concrete
 * class.
 */
interface RepositoryInterface
{
}
