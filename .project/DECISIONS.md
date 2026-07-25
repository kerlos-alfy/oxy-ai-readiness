# Decisions

Canonical, ongoing decision log starting at Phase 1. Phase 0/0.5 decisions (including the 5 ADRs) are recorded in `.project/08-Decision-Log.md` and `.project/adr/` (not duplicated here).

## Phase 1 — Repository Foundation — 2026-07-24

**Decision:** `RepositoryInterface` is an empty marker interface, not a contract with required methods.
**Context:** `docs/04-Folder-Structure.md` lists one shared `Contracts/RepositoryInterface.php` for five repositories (Options, Post, File, Transient, User) that each wrap a completely different native WordPress storage API with no meaningfully shared method signature.
**Rationale:** Forcing a common method surface (e.g. a generic `find()`/`save()`) across Options/Transient (key-value), Post (WP content), File (filesystem), and User (identity) would produce awkward, misleading abstractions. The marker interface still enables Service Container tagging/discovery, matching the tagging pattern in `docs/29-Developer-Guide.md`'s `ServiceProvider` example.
**Affects:** `app/Contracts/RepositoryInterface.php`, all five repositories, future repositories added in later phases.

**Decision:** The five foundation repositories wrap *native* WordPress storage (options, transients, posts, users, filesystem) — none of them touch a custom `oxy_*` table.
**Context:** The user's Phase 1 scope ("Repository Foundation... do not implement Phase 2 or later") excludes the database migration work that was Phase 2 in the originally proposed plan (`.project/06-Phase-Plan.md`).
**Rationale:** `docs/04-Folder-Structure.md`'s named repository list (`OptionsRepository`, `PostRepository`, `FileRepository`, `TransientRepository`, `UserRepository`) maps exactly onto WordPress-native storage — none of the five require a custom table to exist. This makes Repository Foundation genuinely independent of the migration system, honoring "do not skip phases" / "do not implement Phase 2" while still being a complete, useful, testable slice of the architecture.
**Affects:** Scope boundary for Phase 1; Phase 2 will add table-backed repositories once migrations exist.

**Decision:** `FileRepository` receives its base directory and (optionally) an already-resolved `WP_Filesystem_Base` instance via constructor injection, rather than resolving `wp_upload_dir()` / calling `WP_Filesystem()` internally by default.
**Context:** Needed a way to make filesystem-touching code unit-testable under Brain Monkey (which mocks functions, not the `$wp_filesystem` global object) without requiring a real WordPress bootstrap or touching the real disk.
**Rationale:** Constructor DI lets tests inject a Mockery mock of `WP_Filesystem_Base` directly — clean, explicit, no global-state manipulation in tests. Production wiring (a future `ServiceProvider`, Phase 2+) will call `wp_upload_dir()` and `WP_Filesystem()` once and inject the results.
**Affects:** `app/Repositories/FileRepository.php`, its test suite, and the future ServiceProvider that constructs it.

**Decision:** `FileRepository::resolvePath()` rejects *any* path segment equal to `..`, rather than resolving the path and checking whether the result stays inside the base directory.
**Context:** `docs/26-Security-Spec.md` requires canonical Path Validation; `docs/28-Testing-Strategy.md`'s File Security Testing explicitly lists "Relative Paths" and "Invalid Paths" as required test cases.
**Rationale:** `realpath()`-based resolve-then-check does not work reliably for files that do not exist yet (the write case) since `realpath()` returns `false` for non-existent paths. A blanket "no `..` segment, no absolute path, no null byte" rule is simpler, has no edge cases, and is easy to verify by inspection — preferred over a more permissive but harder-to-verify resolve-and-compare strategy.
**Affects:** `app/Repositories/FileRepository.php`.

**Decision:** `PostRepository::query()` throws `InvalidArgumentException` on `posts_per_page = -1` (WordPress's own "unbounded" convention) and clamps any larger value to 100.
**Context:** `docs/27-Performance-Spec.md`: "Avoid unbounded queries", "Maximum Rows Per Request 100 default".
**Rationale:** A repository is the correct place to enforce this system-wide rule once, rather than trusting every future caller to remember it.
**Affects:** `app/Repositories/PostRepository.php`.

**Decision:** Composer/tooling config (`composer.json`, `phpcs.xml`, `phpstan.neon`, `phpunit.xml.dist`) was created in Phase 1, even though Phase 0.5 had an explicit "do not create composer.json" restriction.
**Context:** That restriction was scoped explicitly to Phase 0.5 ("Do NOT write any production code. Do NOT create app/, assets/, composer.json or plugin files."). Phase 1's instructions are different: "Begin Phase 1... Implement only the Repository Foundation" — implementation is now in scope, and PSR-4-autoloaded PHP classes are not meaningfully "implementable" or checkable without a `composer.json`.
**Rationale:** Treated as necessary minimal scaffolding for the requested deliverable, not scope creep — deliberately excluded the plugin bootstrap file, `uninstall.php`, `readme.txt`, and `package.json` (frontend tooling), none of which the Repository Foundation needs.
**Affects:** Repo root. Flagged explicitly in `PHASE-1-REPORT.md` in case the user intended a narrower interpretation.

## Phase 1 validation pass — 2026-07-24 (real PHP/Composer/Node/npm/Git now available)

**Decision:** `phpcs.xml` was rewritten from a bare `WordPress-Extra` ruleset to a hybrid standard: PSR-12 governs formatting and naming; `WordPress.Security`, `WordPress.WP`, `WordPress.DB`, and `WordPressVIPMinimum.Security` are kept for WordPress-specific security/API-usage/i18n sniffs. Added `automattic/vipwpcs` to `require-dev` for the VIP-minimum sniffs.
**Context:** Running PHPCS for the first time (it had never actually executed before this session — no PHP was available) against the original bare-`WordPress-Extra` ruleset produced **1,877 errors across all 15 Phase 1 files**, 1,848 of them auto-fixable — almost entirely tabs-vs-spaces, snake_case-vs-camelCase variable/property naming, and K&R-vs-Allman brace placement. `docs/29-Developer-Guide.md`'s Coding Standards section lists both "WordPress Coding Standards" and "PSR-12 where compatible", and its Naming Conventions explicitly state Methods: camelCase, Typed Properties, Readonly properties — conventions that are structurally incompatible with WordPress-Extra's literal procedural-style formatting rules. The actual Phase 1 code was written consistently in the PSR-12/camelCase style the docs describe, not classic WordPress procedural style.
**Rationale:** This was surfaced to the user directly (`AskUserQuestion`) rather than resolved unilaterally, given the scale (touches every file) and the fact that either resolution — rewrite ~1,500 lines to snake_case/tabs, or narrow the ruleset — sets a binding precedent for every future phase's code style. The user chose the hybrid ruleset explicitly: "Use a hybrid PHPCS ruleset. Keep: PSR-12 as the coding style. WordPress-Extra security sniffs. WordPress.Security.*, WordPress.WP.*, WordPress.DB.*, WordPressVIPMinimum.Security.*. Exclude formatting and naming sniffs that conflict with PSR-12... Do not rewrite the codebase to procedural WordPress style."
**Affects:** `phpcs.xml`, `composer.json` (`automattic/vipwpcs` dependency), and the coding-standard precedent for every file written in every later phase — all future PHP code should target this hybrid standard, not literal `WordPress-Extra`.

**Decision:** Three narrow, path- or error-code-scoped PHPCS exclusions were added on top of the hybrid ruleset (not blanket sniff disables) to resolve the remaining 64 errors/2 warnings after the ruleset switch.
**Context:** After switching to the hybrid ruleset, three categories of finding remained, none of them real defects: (1) `PSR1.Methods.CamelCapsMethodName.NotCamelCaps` firing on every PHPUnit `test_snake_case_description` method name — the standard, intentional PHPUnit test-naming convention; (2) `PSR1.Classes.ClassDeclaration.{MissingNamespace,MultipleClasses}` and `Squiz.Classes.ValidClassName.NotCamelCaps` firing on `tests/stubs/wp-core-stubs.php`'s `WP_User`/`WP_Post`/`WP_Filesystem_Base` — these intentionally mirror real WordPress core's actual non-namespaced, snake_case-method global classes, not this project's own convention; (3) `WordPress.Security.EscapeOutput.ExceptionNotEscaped` firing on every `sprintf()` used to build an `InvalidArgumentException`/`RuntimeException` message — none of which are ever echoed to a browser, the context that sniff targets.
**Rationale:** Each exclusion is scoped as narrowly as PHPCS allows (by `exclude-pattern` to `tests/*` or `tests/stubs/*`, or by the sniff's own specific error code rather than its whole category) so real enforcement — camelCase methods in `app/`, proper namespacing for actual project classes, and genuine output-escaping (`OutputNotEscaped` etc.) — stays fully active everywhere else.
**Affects:** `phpcs.xml`. Two additional non-ruleset fixes: `RepositoryContractTest.php` (wrapped a 124-character line) and `PostRepositoryTest.php` (inline `phpcs:ignore` on a test that deliberately passes an out-of-range `posts_per_page` of 5000 to exercise the documented 100-row clamp).

**Decision:** `composer test` now runs `phpunit --no-coverage`; a separate `test:coverage` script (`phpunit`, no flag) was added for when a coverage driver is available.
**Context:** `phpunit.xml.dist` unconditionally requests HTML/text coverage reports. No Xdebug or PCOV driver is installed on the available PHP build (`C:\php`), so any plain `phpunit`/`composer test` run failed with a PHPUnit-runner warning ("No code coverage driver available") that turns the overall run's exit code non-zero even though all tests pass.
**Rationale:** Splitting "fast test run" from "coverage-enforced run" is standard practice and doesn't touch the coverage configuration itself (still present in `phpunit.xml.dist` for whenever a driver exists) — it just stops the default `test` script from failing over an environment capability gap rather than an actual test failure.
**Affects:** `composer.json`. **Flagged as an open risk, not resolved**: `docs/28-Testing-Strategy.md`'s coverage thresholds (80% minimum, 90–95% for core/security/scoring/auto-fix/REST) cannot be measured or gated until a coverage driver is installed — install `pcov` (faster) or `xdebug` (mode `coverage`) before trusting coverage numbers on any later phase, especially security/scoring/auto-fix work.

**Decision:** Fixed a real bug in `FileRepository::write()`, found by PHPStan (level 8), not invented scope creep.
**Context:** The original code called `$fs->mkdir($directory, 0755, true)`, modeled on PHP's native `mkdir($path, $mode, $recursive)` signature. `WP_Filesystem_Base::mkdir()`'s actual signature (confirmed against `php-stubs/wordpress-stubs`) is `mkdir($path, $chmod = false, $chown = false, $chgrp = false)` — there is no recursive-creation option at all. `true` was silently being passed as `$chown` (a user login name or `false`), and nested directories more than one level below the configured base would never actually have been created — a genuine, previously-undetected bug (undetectable by the manual review done before PHP was available, since it's a subtle real-vs-assumed-API-shape mismatch, not a syntax error).
**Rationale:** Added a private recursive `ensureDirectoryExists()` helper that walks from the target directory up to `$baseDir` (assumed pre-existing, consistent with the class's existing path-confinement design), creating each missing level individually via `$fs->mkdir($dir, 0755)`. This is a correctness fix within the existing design, not a redesign — `FileRepository`'s public API, `resolvePath()`'s confinement logic, and every other method are unchanged.
**Affects:** `app/Repositories/FileRepository.php`, `tests/Unit/Repositories/FileRepositoryTest.php` (updated the `mkdir` mock's expected argument list from 3 args to 2).

**Decision:** Added an empty `tests/Integration/` directory (`.gitkeep` placeholder only) rather than writing integration tests.
**Context:** `phpunit.xml.dist` declares both `Unit` and `Integration` testsuites, but only `tests/Unit/` existed — PHPUnit fails at configuration-parse time (before running anything) if any declared testsuite's directory is missing, which broke every invocation that didn't pass `--testsuite=Unit` explicitly, including the plain `phpunit`/`composer test` commands used for validation.
**Rationale:** Writing integration tests is out of scope for Phase 1 ("do not implement Phase 2 or later" / the user's explicit "do not start Phase 2" instruction this session) — the goal was only to make the existing config internally consistent, not to add new test coverage.
**Affects:** `tests/Integration/.gitkeep` (new, empty). `composer.json`'s existing `test:integration` script (`phpunit --testsuite=Integration`) will now run cleanly (0 tests) instead of erroring, once real integration tests are added in a later phase.

## Phase 2 — Foundational Scaffolding — 2026-07-25

**Decision:** Phase 2 is scoped to the deferred "Foundational Scaffolding" (bootstrap file, Container, `Core/`, activation lifecycle) rather than the originally-drafted Phase 2 ("Database & shared infrastructure": migrations, tables, Settings Manager, Logger, Cache Service, Queue).
**Context:** The draft plan (`06-Phase-Plan.md`) assumed its own Phase 1 (scaffolding) would already exist before its Phase 2 (DB infra) started. The user's actual, approved Phase 1 was narrowed to only the Repository layer, so the draft's Phase 2 had an unmet prerequisite — no Container/bootstrap exists yet for a migration runner or Settings Manager to register into, per `docs/02-Architecture.md`'s Bootstrap Sequence (Autoloader → Constants → Service Container → Register Services → Core Components → ...).
**Rationale:** Surfaced via `AskUserQuestion` rather than silently picking an interpretation, since either reading (bootstrap-only vs. bootstrap+DB-infra-combined) commits real, hard-to-cheaply-undo scope. User chose bootstrap/Container/Core only, explicitly excluding DB tables/migrations/Settings Manager/Logger/Cache Service/Queue.
**Affects:** Everything built this phase; `Core/Scheduler.php`, `Core/ModuleRegistry.php`, `Core/StandardsRegistry.php`, and all DB-infra work are deferred to a future phase, not this one.

**Decision:** `ServiceProvider` (abstract base class) lives at `app/Providers/ServiceProvider.php` (namespace `OxyAI\Providers`), not `app/Core/Container/ServiceProvider.php` (namespace `OxyAI\Core\Container`) as `docs/29-Developer-Guide.md`'s worked example literally imports it.
**Context:** That example's `use OxyAI\Core\Container\ServiceProvider;` requires `Container` to be a namespace (a folder), which conflicts with `docs/04-Folder-Structure.md`'s canonical Core/ list, where `Container.php` is a flat file (a class, not a folder) — PSR-4 cannot have a class and a namespace share one name.
**Rationale:** Treated the dev-guide's import as illustrative rather than literal, same precedent as Phase 1's `RepositoryInterface` marker-interface decision. Placed the base class at `app/Providers/ServiceProvider.php` instead, which matches the already-documented top-level `Providers/` folder in `docs/04-Folder-Structure.md`'s App structure section — no new folder invented, no naming collision.
**Affects:** `app/Providers/ServiceProvider.php`; the import path every future Core/Module service provider will use.

**Decision:** `Container::bind()`/`singleton()` take zero-argument factory closures (`Closure(): mixed`), not `Closure(Container $c): mixed` auto-wiring closures.
**Context:** Nothing bound into the Container this phase (currently just `Config::class`) needs the container to resolve its own dependencies — `Config` is constructed directly from plugin-file/version strings known at `Plugin::__construct()` time.
**Rationale:** Building auto-wiring (passing the container into every factory, or reflection-based constructor resolution) with no real consumer yet would be exactly the kind of speculative, undemonstrated feature the project's engineering guidelines warn against. Deferred until a later phase's Provider actually needs the container to resolve a factory's own dependencies.
**Affects:** `app/Core/Container.php`, `app/Core/Application.php`'s pass-through methods, and the signature every future `ServiceProvider::register()` call binds against — adding container-aware factories later is additive, not a breaking change.

**Decision:** `Bootstrap::run()` fires a new `oxy_ai_ready` action after marking the `Application` booted, matching the `oxy_ai_` prefix convention `OptionsRepository` already established, rather than inventing an unprefixed or differently-named hook.
**Context:** No hook-naming convention is specified anywhere in `docs/*` for the plugin's own lifecycle events (`docs/22-Plugin-SDK.md`'s "Hook System" only documents per-module before/after events like "Before Generation"/"After Generation"); `docs/29-Developer-Guide.md`'s Bootstrap Flow only names the step "Ready Event" without a literal hook name.
**Rationale:** Reused the one naming convention that does exist in the codebase (`OptionsRepository::PREFIX = 'oxy_ai_'`) rather than inventing a new, unrelated convention, so a later phase's naming-convention doc (if one gets written) has one existing precedent to reconcile against, not two.
**Affects:** `app/Core/Bootstrap.php`. Third-party code or later phases hooking into plugin readiness should use `oxy_ai_ready`.

**Decision:** `Plugin::activate()` uses `OptionsRepository` to record `installed_at` (once) and `version` (every activation); `Plugin::deactivate()` is an intentionally empty method body.
**Context:** CLAUDE.md's "do not store all data in WordPress options" restriction targets using `wp_options` as the general operational datastore (the whole `oxy_*` table schema); `OptionsRepository`'s own Phase 1 docblock explicitly names "installed version, activation timestamp" as its legitimate use case.
**Rationale:** This is that exact, already-approved use case, not new scope — no `oxy_*` table exists yet for this to belong to instead. `deactivate()` has a real, WordPress-required callback registered (`register_deactivation_hook`) but a genuinely empty body: no scheduled events, transients, or caches exist yet to clear. An empty-but-honestly-empty method is not the same as placeholder code claiming to do something it doesn't.
**Affects:** `app/Core/Plugin.php`.

**Decision:** Added a narrowly file-scoped PHPCS exclusion: `PSR1.Files.SideEffects.FoundWithSymbols`, scoped to `oxy-ai-readiness.php` only.
**Context:** The hybrid ruleset (PSR-12 base) flagged the plugin main file for both defining constants (`OXY_AI_READINESS_VERSION` etc.) and executing side effects (the `ABSPATH` guard, requiring the autoloader, registering activation hooks) in one file.
**Rationale:** Every WordPress plugin bootstrap file necessarily does both — that is what the file is for; there is no PSR-1-compliant way to write one. Scoped by `exclude-pattern` to that one filename only (same narrow-exclusion pattern as Phase 1's three), not a project-wide sniff disable.
**Affects:** `phpcs.xml`, `oxy-ai-readiness.php`.

**Decision:** Extended `phpstan.neon`'s `paths` and `phpcs.xml`'s `<file>` list to include the two new root plugin files (`oxy-ai-readiness.php`, `uninstall.php`), which previously only covered `app/`/`tests/`.
**Context:** Root-level plugin files didn't exist before this phase; static analysis/linting had nothing to scope to there.
**Rationale:** Keeping the "0 errors, 0 warnings" bar meaningful requires covering every hand-authored PHP file, not just `app/`.
**Affects:** `phpstan.neon`, `phpcs.xml`.

**Decision:** Fixed three PHPUnit "risky" (no-assertions) warnings by using Brain Monkey's real simulated hook storage (`Actions\has()`) where one exists, and `expectNotToPerformAssertions()` where only a Mockery expectation applies (`get_option`/`update_option`, which Brain Monkey does not simulate for real) — not by adding a hollow `self::assertTrue(true)`.
**Context:** `Plugin::run()`/`activate()`/`deactivate()` are void methods; tests that only set Mockery/Brain-Monkey expectations (verified in `tearDown()`) register zero assertions from PHPUnit's own point of view, so PHPUnit correctly flags them as risky.
**Rationale:** `expectNotToPerformAssertions()` is PHPUnit's documented mechanism for exactly this case — a test whose correctness is verified by a non-PHPUnit assertion library — and is more honest than a placeholder assertion that verifies nothing.
**Affects:** `tests/Unit/Core/PluginTest.php`.
