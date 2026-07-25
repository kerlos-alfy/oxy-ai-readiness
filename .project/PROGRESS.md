# Progress

Canonical, ongoing progress record starting at Phase 1. Phase 0 and Phase 0.5 history is preserved in `.project/07-Progress-Log.md` (not duplicated here).

## Phase 1 — Repository Foundation — 2026-07-24

**Status: Complete and validated for real. Awaiting approval before Phase 2.**

### Validation pass — 2026-07-24 (same day, local PHP/Composer/Node/npm/Git now installed)

The original Phase 1 build below was done with no PHP interpreter available, so nothing had actually been executed — only manually reviewed. This session re-ran everything for real per the user's instruction (canonical PHP: `C:\php\php.exe`; `C:\xampp\php` explicitly excluded). Full detail in `TEST-STATUS.md` and `PHASE-1-REPORT.md`; summary:

- Fixed three local environment blockers (none were code defects): `php_zip.dll` was present but disabled in `php.ini` (composer install couldn't unzip packages); `phpstan`'s 128M default memory limit was too low for the WordPress stubs (ran with `--memory-limit=512M`); `phpcs.xml` referenced the `PHPCompatibilityWP` sniff without its Composer package ever being added (added `phpcompatibility/phpcompatibility-wp`).
- PHPStan (level 8) caught one real bug: `FileRepository::write()` passed `true` as `WP_Filesystem_Base::mkdir()`'s third argument intending "recursive", but that method's real signature has no recursive flag (third/fourth args are `$chown`/`$chgrp`) — nested directories would never have actually been created. Fixed with a proper recursive `ensureDirectoryExists()` helper; updated the corresponding test's mock expectation.
- PHPCS against a bare `WordPress-Extra` ruleset produced 1,877 errors — almost entirely the ruleset's classic-procedural-WordPress formatting (tabs, snake_case variables, K&R braces) fighting the codebase's documented PSR-12/camelCase-methods convention (`docs/29-Developer-Guide.md`), not real problems. Per user decision (asked and confirmed), rewrote `phpcs.xml` as a hybrid: PSR-12 for formatting/naming, `WordPress.Security`/`WordPress.WP`/`WordPress.DB`/`WordPressVIPMinimum.Security` kept for WP-API/security sniffs (added `automattic/vipwpcs`). Three narrow, justified exclusions remain (test-method naming, WP-core stub class naming, exception-message false-positive on `EscapeOutput`) — see `DECISIONS.md`.
- Also added the `tests/Integration/` directory (empty, `.gitkeep` only) — `phpunit.xml.dist` declared an `Integration` testsuite that didn't exist yet, which fails PHPUnit's config parser outright on any run without `--testsuite=Unit`. No integration tests were added; still explicitly out of scope for Phase 1.
- **Final state, all exit code 0:** `composer install`, `composer validate`, `composer test` (54 tests, 57 assertions), PHPUnit direct, PHPStan (0 errors), PHPCS (0 errors, 0 warnings), `composer quality`.
- **Known gap, not a Phase 1 blocker:** no code-coverage driver (Xdebug/PCOV) installed — coverage thresholds from `docs/28-Testing-Strategy.md` are not measurable yet. `composer test` now runs `--no-coverage` explicitly; `composer test:coverage` is available once a driver exists. Flagged as a risk for later phases, especially security/scoring/auto-fix code with 90–95% coverage requirements.

Full manifest of files touched this pass is in `FILE-MANIFEST.md`; full command-by-command log is in `TEST-STATUS.md`.

Scope: implemented only the Repository layer described in `docs/02-Architecture.md` (Repository Pattern) and `docs/04-Folder-Structure.md` (canonical, post-ADR-002) — the five shared repositories (`OptionsRepository`, `TransientRepository`, `UserRepository`, `PostRepository`, `FileRepository`) plus their `RepositoryInterface` contract, wired to native WordPress storage APIs (options, transients, users, posts, filesystem). No custom `oxy_*` database tables, no migrations, no Service Container, no bootstrap, no modules, no REST — those belong to later phases.

### What was built
- `composer.json`, `phpcs.xml`, `phpstan.neon`, `phpunit.xml.dist` — minimal tooling needed to autoload and check the Repository layer (PSR-4 `OxyAI\` → `app/`, PHPUnit 10, PHPStan level 8 + WP stubs, WPCS).
- `app/Contracts/RepositoryInterface.php` — marker interface every repository implements.
- `app/Repositories/OptionsRepository.php` — wraps `get_option`/`update_option`/`delete_option`, `oxy_ai_` key prefix, autoload defaults to `false`.
- `app/Repositories/TransientRepository.php` — wraps `get_transient`/`set_transient`/`delete_transient`, includes a `remember()` cache-aside helper.
- `app/Repositories/UserRepository.php` — wraps `get_userdata`/`get_user_by`/`current_user_can`/`user_can`, normalizes `WP_User` to plain arrays.
- `app/Repositories/PostRepository.php` — wraps `get_post`/`get_posts`, enforces a 100-row cap and rejects unbounded (`-1`) queries per `docs/27-Performance-Spec.md`.
- `app/Repositories/FileRepository.php` — wraps `WP_Filesystem_Base`, confines every path to an injected base directory (rejects traversal/absolute paths/null bytes), atomic write-then-move, SHA-256 checksums, per `docs/26-Security-Spec.md`.
- `tests/bootstrap.php`, `tests/stubs/wp-core-stubs.php`, `tests/Unit/TestCase.php` — PHPUnit + Brain Monkey wiring and minimal local WP class stand-ins (`WP_User`, `WP_Post`, `WP_Filesystem_Base`).
- 6 test files (43 test methods total) covering all 5 repositories plus a contract-conformance test (every repository implements `RepositoryInterface` and is `final`).

### Checks — environment limitation (historical; superseded by the validation pass above)
**No PHP, Composer, Node, npm, or git binary was available in this sandbox at the time this was originally written** (confirmed via `Get-Command`). This meant `composer install`, `phpunit`, `phpstan analyse`, and `phpcs` could not actually be executed — there was no PHP interpreter to run them. What was done instead, as a substitute (not a replacement) for real execution:
1. Every new file was re-read in full after writing and manually reviewed line-by-line against the relevant doc requirements.
2. An automated brace/parenthesis balance check was run across all 15 new PHP files via PowerShell regex counts — all 15 balanced.
3. Every test's mock expectations were manually traced against the corresponding repository method's actual call sequence to confirm they match.

**This is not a substitute for actually running PHPUnit/PHPStan/WPCS.** Static review can catch gross structural errors but cannot catch every type error, autoload misconfiguration, or logic bug the way real execution would. This must be run for real (`composer install && composer quality`) in an environment with PHP 8.1+ and Composer before this code is trusted in Phase 2 or merged — flagged as a Phase 1 risk in `PHASE-1-REPORT.md`.

### Files created (16 production/tooling + 9 test = the full manifest is in `FILE-MANIFEST.md`)

### Explicitly out of scope for Phase 1 (deferred to later phases per the approved phase plan)
- Plugin bootstrap file (`oxy-ai-readiness.php`), `uninstall.php`, `readme.txt`
- Service Container, `Core/` (Bootstrap, Kernel, Hooks, Loader, ModuleRegistry, StandardsRegistry)
- Custom `oxy_*` database tables / migrations (Phase 2)
- Any Module, any Standard, any REST route
- `package.json` / frontend tooling (no frontend work in this phase)

## Phase 2 — Foundational Scaffolding — 2026-07-25

**Status: Complete, all checks run for real and passing. Approved by the user 2026-07-25.**

### Scope resolution before starting

The originally-drafted `06-Phase-Plan.md` had its own Phase 1 = "Foundational Scaffolding" (bootstrap, Container, `Core/`) and Phase 2 = "Database & shared infrastructure" (migrations, tables, Settings Manager, Logger, Cache Service, Queue). Since the user's actual, approved Phase 1 was narrowed to only the Repository layer, the draft's Phase 2 had an unmet prerequisite: no bootstrap file, Container, or `Core/` existed for migrations/services to register into (`docs/02-Architecture.md`'s Bootstrap Sequence: Autoloader → Constants → Service Container → Register Services → Core Components → ...). Surfaced this gap to the user directly rather than assuming; the user chose to scope this Phase 2 as the deferred Foundational Scaffolding only, explicitly excluding database tables/migrations/Settings Manager/Logger/Cache Service/Queue (pushed to a future phase).

### What was built

- `oxy-ai-readiness.php` — plugin header (name, description, version 0.1.0, min WP 6.5, min PHP 8.1, text domain), a PHP-version guard (admin notice + early return if PHP < 8.1, before the autoloader is even required), then constructs `Core\Plugin` and registers activation/deactivation hooks.
- `uninstall.php` — `WP_UNINSTALL_PLUGIN` guard only. No cleanup logic written, since nothing persistent exists yet beyond the two option keys `Plugin::activate()` sets.
- `readme.txt` — WP-standard plugin readme header + changelog.
- `app/Core/Container.php` — minimal DI container (`bind`/`singleton`/`make`/`has`); factories are zero-argument by design (no bound service yet needs the container to resolve its own dependencies — auto-wiring deferred until a real consumer needs it).
- `app/Core/Application.php` — holds the `Container`, tracks boot state (`isBooted()`/`markBooted()`), thin `make`/`has`/`bind`/`singleton` pass-through.
- `app/Core/Config.php` — plugin-level metadata only (version, plugin file/dir, text domain) — explicitly not the module-level `config/*.php` files in `docs/04-Folder-Structure.md`, none of which have an owning module yet.
- `app/Core/Hooks.php` — registrar wrapping `add_action`/`add_filter` with bookkeeping, so later Providers register hooks declaratively through one testable object.
- `app/Core/Bootstrap.php` — idempotent boot sequence: marks the `Application` booted and fires a new `oxy_ai_ready` action (the "Plugin Ready" step of the documented Bootstrap Sequence). "Register Services"/"Load Core Components"/"Load Enabled Modules" have nothing to do yet — no Providers or Modules exist.
- `app/Core/Kernel.php` — the only class that decides *when* the plugin boots: registers on WordPress's `plugins_loaded` and delegates to `Bootstrap` when fired.
- `app/Core/Plugin.php` — top-level object instantiated by the bootstrap file; wires Container → Application → Bootstrap → Kernel; `activate()` genuinely uses Phase 1's `OptionsRepository` to record `installed_at`/`version` (exactly the narrow use case that repository's own docblock describes); `deactivate()` is an intentionally empty, real (not placeholder) no-op since nothing exists yet to tear down.
- `app/Providers/ServiceProvider.php` — abstract base class (`register()`/`boot()`) every future Core/Module provider extends.
- 8 new test files, 24 new test methods (`ContainerTest` 5, `ApplicationTest` 3, `ConfigTest` 3, `HooksTest` 2, `BootstrapTest` 2, `KernelTest` 2, `PluginTest` 5, `ServiceProviderTest` 2).

### Checks — all run for real, all passing

`composer validate` (valid), `composer test` → `OK (78 tests, 89 assertions)` (54 from Phase 1 + 24 new), PHPStan level 8 (now also scoped to the two root plugin files) → `[OK] No errors`, PHPCS (hybrid ruleset, now also linting `oxy-ai-readiness.php`/`uninstall.php`) → 0 errors/0 warnings across 33 files, `composer test:integration` → 0 tests (still no integration tests, unchanged from Phase 1), `composer quality` → all green.

Three tests initially came back "risky" (Mockery/Brain-Monkey expectations verified in `tearDown()` aren't PHPUnit-native assertions, so PHPUnit correctly flags a test with none of its own). Fixed by either asserting against Brain Monkey's real (simulated, not mocked) hook storage where possible (`Actions\has('plugins_loaded')`), or calling `expectNotToPerformAssertions()` where a WordPress function genuinely has no real-storage simulation (`get_option`/`update_option`) and only a Mockery expectation applies — not by adding a hollow `assertTrue(true)`.

One PHPCS finding required a narrowly-scoped ruleset exclusion, same pattern as Phase 1's three: `oxy-ai-readiness.php` trips `PSR1.Files.SideEffects.FoundWithSymbols` because every WordPress plugin main file necessarily both defines constants and executes activation-hook side effects — scoped to that one file only, not a blanket disable (see `DECISIONS.md`).

**Known gap, flagged not hidden (same honesty as Phase 1's coverage-driver gap):** "plugin activates cleanly on a clean WordPress install" — the original Phase 1 draft's exit criterion — still cannot be verified in this sandbox. There is no real WordPress install here, only Brain Monkey's simulated hook functions. Everything unit-testable was verified for real; the true activation smoke test remains open until run against an actual WP instance.

### Explicitly out of scope for Phase 2 (deferred)
- `Core/Scheduler.php`, `Core/ModuleRegistry.php`, `Core/StandardsRegistry.php` (Queue/Scheduler infra and the Module/Standard SDK belong to later phases, not scaffolding)
- Any custom `oxy_*` database table, migration runner, Settings Manager, Logger service, Cache Service, Queue
- Any Module, any Standard, any REST route, any admin UI
- `package.json` / frontend tooling

## Phase 3 — Module & Standard SDK skeleton — 2026-07-25

**Status: Complete, all checks run for real and passing. Approved by the user 2026-07-25.**

### Scope resolution before starting

The user said "start Phase 3 exactly according to the roadmap." Read literally against `06-Phase-Plan.md`'s own numbering, its row 3 is "Module & Standard SDK skeleton" (`ModuleInterface`, `StandardInterface`, Module Registry, Standards Registry, `ServiceProvider` pattern, module lifecycle wiring, one minimal internal "probe" module/standard for validation only, not user-facing), listed as depending on the draft's own Phase 2 ("Database & shared infrastructure") — which still doesn't exist, since our actual Phase 2 was the deferred scaffolding (the draft's Phase 1), not the draft's Phase 2. Proceeded anyway because the roadmap row's own exit criterion ("A module can be enabled/disabled at runtime without touching core; events fire on lifecycle transitions") does not require persistence — in-memory registry state is sufficient for a skeleton, consistent with the row's own framing of a minimal, internal, not-user-facing probe. Documented as a decision rather than asked again, since the user's phrasing ("exactly according to the roadmap") read as a direct scope instruction, not an open question.

### What was built

- `app/Contracts/ModuleInterface.php` / `StandardInterface.php` — the full documented contracts (docs/05-Modules.md, docs/22-Plugin-SDK.md, docs/23-AI-Standards-Layer.md), not narrowed.
- `app/Exceptions/ModuleException.php` — registry bookkeeping errors and "nothing to delegate to yet" errors.
- `app/Events/` — 7 lifecycle event DTOs (`ModuleRegistered`/`Booted`/`Enabled`/`Disabled`, `StandardRegistered`/`Enabled`/`Disabled`), fired via `do_action` (reusing Phase 2's `Bootstrap`-proven pattern, not a new event/listener system).
- `app/Core/ModuleRegistry.php` / `StandardsRegistry.php` — register/enable/disable/get/has/all (+ `boot`/`remove` for Modules specifically), in-memory only, firing the events above.
- `app/Core/CoreServiceProvider.php` — the first real consumer of Phase 2's `ServiceProvider` base class; binds both registries as Container singletons.
- `app/Modules/Probe/` — `ProbeModule`, `ProbeStandard`, `ProbeServiceProvider`: the one internal, not-user-facing probe named in the roadmap row. `ProbeModule`'s `assets()`/`routes()`/`settings()`/`permissions()`/`audit()` honestly return empty arrays (it has none of these yet, and none of the subsystems they'd register into exist). `ProbeStandard`'s `discover()`/`generate()`/`validate()`/`score()`/`monitor()`/`report()` throw `ModuleException` — the owning module has no Discovery provider/Generator/Validator/ScoreProvider/Monitor/Reporter registered, since those engines are later phases; throwing reports that honestly instead of fabricating a result.
- `app/Core/Bootstrap.php` (modified) — now takes an ordered `ServiceProvider[]` list; `run()` calls every `register()`, then every `boot()`, before marking the app booted — this is where "Register Services"/"Load Core Components"/"Load Enabled Modules" (previously "nothing to do yet" in Phase 2) get filled in for real.
- `app/Core/Plugin.php` (modified) — constructs `[CoreServiceProvider, ProbeServiceProvider]` and passes them to `Bootstrap`; added `Plugin::boot()` (a thin passthrough to `Kernel::boot()`) since Brain Monkey's simulated `add_action`/`do_action` track hook registration but do not themselves invoke registered callbacks — tests need a direct way to exercise the same path WordPress would.
- 8 new/extended test files, testing the registries, the ServiceProvider wiring, the probe module/standard, and — in `PluginTest`/`BootstrapTest` — the full chain end-to-end through the real `Container`/`Application`/`Bootstrap`/`Kernel`.

### Checks — all run for real

`composer validate` (valid), `composer test` → `OK (109 tests, 142 assertions)` (up from 78/89), PHPStan level 8 → `[OK] No errors`, PHPCS hybrid ruleset → 0 errors/0 warnings across 55 files, `composer test:integration` → 0 tests (unchanged), `composer quality` → all green.

**One real gap caught and fixed, not just a style nit:** `phpstan.neon` still had `excludePaths: [app/Modules/*]` inherited from Phase 1 (added before any Module existed, presumably as a forward-looking placeholder). With `app/Modules/Probe/*` now real code, that exclusion meant PHPStan's "no errors" was silently skipping those three files entirely — a false-clean result, not a true one. Removed the exclusion and re-ran; genuinely 0 errors across all 32 analysed files (up from 29).

### Explicitly out of scope for Phase 3 (deferred)
- `Core/Scheduler.php` (Queue/Scheduler infra — later phase)
- Any custom `oxy_*` database table, migration runner, Settings Manager, Logger service, Cache Service, Queue
- Any real, user-facing Module (Robots/LLMS/Headers/Markdown/etc. — Phase 8 per the draft plan) — only the internal, not-user-facing probe exists
- Discovery/Validation/Generation/Scoring/Monitoring/Reporting engines and their DTOs (Phases 4–7, 9, 13 per the draft plan) — this is why `ProbeStandard`'s six delegate methods throw rather than return real data
- Any REST route, any admin UI
- `package.json` / frontend tooling
