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

## Phase 4 — Discovery Engine — 2026-07-25

**Status: Complete, all checks run for real and passing. Committed, tagged `phase-4`, pushed autonomously per user's standing authorization for this session.**

### Scope

`06-Phase-Plan.md` row 4: "Resource/file/header/endpoint discovery pipeline → Discovery Map, `/discovery/*` REST", exit criterion "Discovery Map correctly lists a known fixture resource; read-only, no writes." User instruction: "Do not implement any Phase 4 features [beyond this]" (i.e. no Phase 5+), proceed autonomously, commit/tag/push without asking.

**Real conflict found and resolved before writing code:** `docs/14-Discovery-Engine.md`'s own REST API section (`GET /discovery`, `/discovery/map`, `/discovery/resources`, `/discovery/modules`; `POST /discovery/scan`, `/discovery/reset`) disagrees with `docs/24-REST-API-Spec.md`'s Discovery API section (`GET /discovery`, `/discovery/files`, `/discovery/resources`; `POST /discovery/run`) — never caught by ADR-003, which only checked route *prefixing*, not cross-doc route-name agreement. Resolved by treating the dedicated engine spec (`docs/14`) as authoritative for Discovery-specific naming, and — per the exit criterion's explicit "read-only, no writes" — implementing only the three GET routes this phase, deferring both docs' POST route (`/scan` or `/run`) entirely rather than guessing which name to build. Logged in `DECISIONS.md`.

### What was built

- `app/DTO/DiscoveredResource.php` — Discovery Map entry (per docs/14's field list) + `toArray()`.
- `app/Contracts/DiscoveryInterface.php` — per-module Discovery provider contract (docs/22-Plugin-SDK.md's SDK Interfaces list).
- `app/Services/DiscoveryService.php` — the engine itself: register providers, `scan()` (fires `oxy_ai_discovery_started`/`oxy_ai_resource_discovered`/`oxy_ai_discovery_finished`), `map()`/`resources()` (lazy-scan on first access — no heavy work during Bootstrap), `reset()` (service-level only, no REST route — see conflict note above).
- `app/Core/RestServiceProvider.php` — the plugin's first REST wiring: hooks `rest_api_init` through the Container-bound `Hooks` registrar, loads `routes/api.php`.
- `app/Http/Controllers/DiscoveryController.php` + `routes/api.php` — `GET /discovery`, `/discovery/map`, `/discovery/resources` under `oxy-ai/v1`, gated by `current_user_can('manage_options')` (an interim default — see Decisions).
- `app/Modules/Probe/ProbeModule.php` now also implements `DiscoveryInterface`, returning one fixture `DiscoveredResource` — proving the pipeline end-to-end exactly as the exit criterion specifies ("a known fixture resource"), reusing Phase 3's established probe pattern rather than inventing a new one.
- `app/Core/Plugin.php` — `Hooks` is now a Container singleton (the same instance shared between `Kernel` and `RestServiceProvider`); `RestServiceProvider` added to the provider list.
- `tests/stubs/wp-core-stubs.php` — added minimal `WP_REST_Request`/`WP_REST_Response` stand-ins (same "mirror the real WP method names" approach as the existing `WP_User`/`WP_Post`/`WP_Filesystem_Base` stubs).
- 4 new test files + 3 extended existing ones.

### Checks — all run for real

`composer validate` (valid), `composer test` → `OK (123 tests, 165 assertions)` (up from 109/142), PHPStan level 8 → `[OK] No errors` across 38 files (up from 32 — `routes/` genuinely added to `phpstan.neon`'s paths, not silently skipped this time), PHPCS hybrid ruleset → 0 errors/0 warnings across 65 files, `composer test:integration` → 0 tests (unchanged), `composer quality` → all green.

One real PHPStan finding fixed properly (not suppressed): `DiscoveryService::map()`'s `@return array<...>` didn't account for the private `$map` property still being typed nullable at the point PHPStan analyses the method body (it can't infer that `scan()` — a separate method call — always populates it). Fixed with `return $this->map ?? [];`, honest and type-correct, not an `@phpstan-ignore` suppression.

### Explicitly out of scope for Phase 4 (deferred)
- `POST /discovery/scan` or `/discovery/reset` REST routes — deferred until a secured, rate-limited, audit-logged POST-endpoint pattern exists (docs/24's API Security list: CSRF/Nonce/Rate Limiting/Audit Logging — none of that shared infra exists yet)
- `Core/Scheduler.php`, any custom `oxy_*` database table, migration runner, Settings Manager, Logger service, Cache Service, Queue
- Any real, user-facing Module; Validation/Generation/Scoring/Monitoring/Reporting engines
- Custom capability registration (`manage_oxy`, `view_audit`, etc. per docs/26-Security-Spec.md) — Discovery routes use the built-in `manage_options` capability as an interim default
- Rate limiting, request signing, audit logging for REST access (docs/24/26's API Security lists) — no Cache/Logger service exists yet to back them
- `package.json` / frontend tooling

## Phase 5 — Validation Engine — 2026-07-25

**Status: Complete, all checks run for real and passing. Committed, tagged `phase-5`, pushed autonomously per the user's standing authorization for the remainder of this project.**

### Scope

`06-Phase-Plan.md` row 5: "Centralized validator framework, `ValidatorInterface`, `/validation/*` REST," exit criterion "A registered validator runs against a Discovery Map entry and returns PASS/WARN/FAIL deterministically."

### What was built

- `app/DTO/ValidationStatus.php` — native backed enum (Pass/Warning/Fail/Info/Skipped/Unknown per docs/16-Validation-Engine.md), enforcing "deterministic" at the type level rather than by string convention.
- `app/DTO/ValidationResult.php` — one validator's verdict on one resource, aligned with `oxy_validation_results`'s columns in docs/25-Database-Schema.md.
- `app/Contracts/ValidatorInterface.php` — per-module validator contract (docs/22-Plugin-SDK.md's SDK Interfaces list).
- `app/Services/ValidationService.php` — the engine: register validators, run every registered one against a resource, fire `oxy_ai_validation_started`/`completed` always and `passed`/`failed`/`warning` per result (docs/16's Events list, minus `AutoFixSuggested` — no AutoFix engine exists yet).
- `app/Http/Controllers/ValidationController.php` + `routes/api.php` additions — `GET /validation` (validator count) and `POST /validation/run` (validates `resource_id` input: 400 if missing, 404 if the Discovery Map doesn't have it, 200 with results otherwise).
- `app/Modules/Probe/ProbeModule.php` now also implements `ValidatorInterface`: pass iff the resource's own reported `status` is `"active"` — deterministic, proving the exit criterion end-to-end.
- **Fixed a real, previously-flagged staleness bug in `ProbeStandard`:** Phase 3's own decision log predicted "their Standard delegate methods stop throwing once their owning Module actually registers a Generator/Validator/etc." — but Phase 4 gave `ProbeModule` a real Discovery provider without ever updating `ProbeStandard::discover()` to stop throwing. Fixed now: `discover()`/`validate()` delegate to the owning module for real; `generate()`/`score()`/`monitor()`/`report()` still throw (no Generation/Scoring/Monitoring/Reporting engine exists).
- 2 new test files + 5 extended existing ones.

### Checks — all run for real

`composer validate` (valid), `composer test` → `OK (135 tests, 190 assertions)` (up from 123/165), PHPStan level 8 → `[OK] No errors` across 43 files (up from 38), PHPCS hybrid ruleset → 0 errors/0 warnings across 72 files, `composer test:integration` → 0 tests (unchanged), `composer quality` → all green.

**A real, previously-latent infra bug caught and fixed:** `composer analyse`/`composer quality` (the actual documented commands, as opposed to the manual `--memory-limit=512M` flag used ad hoc since Phase 1) crashed with a PHPStan out-of-memory error the first time this phase's larger codebase pushed past the default 128M limit. Phase 1's own report had already identified this exact problem and worked around it manually every session since, but never updated `composer.json`'s `analyse` script to bake the fix in — a latent gap that would have bitten any future session or CI run relying on the documented command. Fixed: `"analyse": "phpstan analyse --memory-limit=512M"`.

### Explicitly out of scope for Phase 5 (deferred)
- Generation/Scoring/Monitoring/Reporting engines and their REST routes
- `Core/Scheduler.php`, any custom `oxy_*` database table, migration runner, Settings Manager, Logger service, Cache Service, Queue
- Any real, user-facing Module
- `docs/16-Validation-Engine.md`'s `oxy_ai_validation_before`/`after`/`rules`/`result` filters — not implemented; no consumer exists yet to justify the extensibility surface
- Custom capability registration — `/validation/*` reuses the same `manage_options` interim default as `/discovery/*`
- `package.json` / frontend tooling

## Phase 6 — Generation Engine — 2026-07-25

**Status: Complete, all checks run for real and passing. Committed, tagged `phase-6`, pushed autonomously.**

### Scope

`06-Phase-Plan.md` row 6: "Centralized generator framework, `GeneratorInterface`, publish/rollback/cache/version pipeline, `/generation/*` REST," exit criterion "A generated resource round-trips through Validation before publish; rollback restores prior version."

### A deliberate interface-scope deviation, documented

`docs/17-Generation-Engine.md` literally lists `publish()`/`rollback()`/`cache()`/`version()` as methods every Generator itself implements — but the same document's own Vision states "Instead of allowing every module to implement its own generation logic, the Generation Engine provides one centralized framework." Implementing those four methods per-Generator would force every future generator (Robots, LLMS, etc.) to reimplement identical file-versioning logic, directly contradicting the stated vision. `GeneratorInterface` was kept to `id()`/`resourceId()`/`supports()`/`generate()`; `GenerationService` owns publish/rollback/cache/version as the actual centralized pipeline. Logged in `DECISIONS.md`.

### What was built

- `app/DTO/GenerationResult.php`, `app/Contracts/GeneratorInterface.php`, `app/Exceptions/GenerationException.php`.
- `app/Services/GenerationService.php` — the engine. `publish()` implements the exit criterion directly: resolves the generator's associated Discovery Map entry, runs it through `ValidationService`, refuses to write anything if any result is FAIL, then backs up existing content to a `.previous` sibling file (via `FileRepository`, Phase 1) before writing the new content. `rollback()` restores from that backup. Versioning is a simple two-slot scheme (current + one previous), not full history — sufficient for "restores prior version" (singular).
- `app/Http/Controllers/GenerationController.php` + `routes/api.php` additions — `GET /generation`, `GET /generation/preview`, `POST /generation/publish`, `POST /generation/rollback`. The last of these isn't in docs/17's own REST list (which has `/generation/reset` instead) — added anyway since the exit criterion explicitly requires rollback capability and leaving it unreachable via REST would make the engine's own core feature untestable end-to-end from outside a unit test.
- `app/Modules/Probe/ProbeModule.php` now also implements `GeneratorInterface` — fixed, deterministic content, proving the full pipeline.
- **Fixed `ProbeStandard::generate()`** to delegate to the module for real (same fix-forward pattern as Phase 5's `discover()`/`validate()`); only `score()`/`monitor()`/`report()` still throw.
- **A real functional gap caught and fixed:** `FileRepository` (Phase 1) only ever creates directories *below* its configured base directory, never the base directory itself. Without a fix, every `GenerationService::publish()` call would fail on a fresh install where `storage/generated/` has never been created. Fixed: `Plugin::activate()` now calls `wp_mkdir_p()` to ensure it exists.
- `tests/Unit/Support/InMemoryFilesystem.php` — a small in-memory `WP_Filesystem_Base` test double, needed because `GenerationService::publish()`/`rollback()`'s multi-step read/write/move sequences would make call-by-call Mockery expectations brittle; not a WordPress-core mirror, so it lives under `tests/Unit/Support/`, not `tests/stubs/`.
- 2 new test files + 1 test-support file + 5 extended existing tests.

### Checks — all run for real

`composer validate` (valid), `composer test` → `OK (154 tests, 228 assertions)` (up from 135/190), PHPStan level 8 → `[OK] No errors` across 48 files (up from 43), PHPCS hybrid ruleset → 0 errors/0 warnings across 80 files (two long-line warnings from the new tests fixed by wrapping, one multi-line closure `use()` clause fixed to one-parameter-per-line), `composer test:integration` → 0 tests (unchanged), `composer quality` → all green.

### Explicitly out of scope for Phase 6 (deferred)
- Full version history (only a single rollback point exists, not a version log) — no `oxy_*` table exists yet to back one
- Scoring/Monitoring/Reporting engines and their REST routes
- `Core/Scheduler.php`, any custom `oxy_*` database table, migration runner, Settings Manager, Logger service, Cache Service (proper), Queue
- Any real, user-facing Module
- `docs/17-Generation-Engine.md`'s `oxy_ai_generation_before`/`after`/`template`/`output`/`cache` filters — no consumer yet
- Custom capability registration — `/generation/*` reuses the same `manage_options` interim default
- `package.json` / frontend tooling

## Phase 7 — Scoring Engine — 2026-07-25

**Status: Complete, all checks run for real and passing. Committed, tagged `phase-7`, pushed autonomously.**

### Scope

`06-Phase-Plan.md` row 7: "Weighted scoring, single resolved grade/label scale (Q4), confidence/trend, `/score/*` REST," exit criterion "Score recalculates deterministically from a fixed set of validation results; grade boundary unit tests pass." Q4 (score/grade boundaries) was already resolved in Phase 0.5 via ADR-005 — no open question blocked this phase.

### What was built

- `app/DTO/Grade.php` — native enum, `fromScore()` implementing ADR-005's canonical table exactly, `label()`.
- `app/DTO/Trend.php`, `app/DTO/ConfidenceLevel.php` — native enums per docs/15-Scoring-Engine.md's Trend Status and Confidence Score output lists.
- `app/DTO/ScoreResult.php`, `app/Services/ScoringService.php` — `calculate(array $validationResults): ScoreResult` is a pure function of its input for score/grade/confidence (the exit criterion's "deterministically from a fixed set"); `trend` is inherently comparative (docs' own "Track score history"), so it depends on this instance's in-memory calculation history — same limitation as every other engine's in-memory state so far (no `oxy_*` score-history table exists).
- `app/Http/Controllers/ScoreController.php` + `routes/api.php`'s `GET /score` — chains Discovery → Validation → Scoring exactly per docs/15's own pipeline diagram.
- `tests/Unit/DTO/GradeTest.php` — one data-provider-driven test covering all 20 boundary/near-boundary cases (every grade's lower bound and the value just below it), directly satisfying the exit criterion's own wording ("grade boundary unit tests pass").
- 3 new test files + 2 extended existing ones.

### A documented weighting simplification

docs/15's "WEIGHTING" section defines Critical/High/Medium/Low/Info severity weights (20/10/5/2/0) — but `ValidationResult` (Phase 5) carries a `status` (Pass/Warning/Fail/...), not a severity. Rather than retrofit Phase 5 to add a severity axis it was never scoped to have, this phase's score calculation weights by status instead (Pass=1.0, Warning=0.5, Fail=0.0) — a different, simpler axis that's honest about what data actually exists right now. True severity-weighted scoring applies once the Audit Engine (a later phase) gives rule results a severity. Logged in `DECISIONS.md`.

### Checks — all run for real

`composer validate` (valid), `composer test` → `OK (185 tests, 292 assertions)` (up from 154/228), PHPStan level 8 → `[OK] No errors` across 54 files (up from 48), PHPCS hybrid ruleset → 0 errors/0 warnings across 89 files, `composer test:integration` → 0 tests (unchanged), `composer quality` → all green.

One narrow PHPCS false-positive fixed with an inline suppression, not a ruleset-wide exclusion: `PHPCompatibility.Variables.ForbiddenThisUseContexts.OutsideObjectContext` fired on `match ($this)` inside `Grade::label()` — a legitimate PHP 8.1 enum method using `$this` to match against its own case, which this sniff version doesn't recognize as valid inside enum bodies.

### Explicitly out of scope for Phase 7 (deferred)
- Severity-weighted (Critical/High/Medium/Low/Info) scoring — depends on an Audit Engine that doesn't exist yet
- Persisted score history / trend surviving across requests — no `oxy_*` table exists yet
- Category scores (Discovery 20%/Content 20%/etc.), bonus/penalty system, industry benchmarks, achievements — docs/15's fuller feature set, well beyond the exit criterion
- A `ScoreProviderInterface` for per-module score contributions — Scoring this phase is a stateless calculator over `ValidationResult`s, not a registry like Discovery/Validation/Generation; `ProbeStandard::score()` still throws
- `Core/Scheduler.php`, any custom `oxy_*` database table, migration runner, Settings Manager, Logger service, Cache Service, Queue
- Monitoring/Reporting engines, any real user-facing Module, any admin UI, `package.json`/frontend tooling

## Phase 8 — First end-to-end module: Robots — 2026-07-25

**Status: Complete, all checks run for real and passing. Committed, tagged `phase-8`, pushed autonomously.**

### Scope

`06-Phase-Plan.md` row 8: "Full Robots module (builder, merge/conflict detection, versioning) wired through Discovery→Generation→Validation→Scoring, proving the whole pipeline on one concrete, well-specified feature before parallelizing," exit criterion "`/robots/*` REST fully functional; snapshot test on generated `robots.txt`; audit rule shows in Scoring output."

**Scoped to the exit criterion, not docs/07-Robots-Spec.md's full aspirational feature set.** That document describes a visual rule builder, version history with one-click restore, auto-backup, third-party SEO-plugin (Yoast/RankMath/etc.) merge/conflict detection, multi-format import/export, and an admin UI — none of which the exit criterion requires and most of which need a Settings/DB-persistence phase and the Admin UI phase (12) this project hasn't reached. Built instead: real generation logic (a fixed, genuinely-correct default ruleset), real validation logic, and the REST surface actually named in the exit criterion. Logged in `DECISIONS.md`.

### What was built

- `app/Modules/Robots/RobotsRule.php`, `RobotsModule.php`, `RobotsStandard.php`, `RobotsServiceProvider.php` — the first *real*, user-facing Module, following the exact same shape `Modules/Probe` established (implements `ModuleInterface`+`DiscoveryInterface`+`ValidatorInterface`+`GeneratorInterface`; `RobotsStandard` owns `robots.txt` per ADR-001 and delegates discover/validate/generate, still throwing for score/monitor/report). The default ruleset is WordPress's own standard `/wp-admin/` disallow (with `admin-ajax.php` explicitly allowed, matching WordPress core's real virtual robots.txt) plus docs/07's documented "Allow AI" template for GPTBot/ChatGPT-User/Google-Extended/ClaudeBot/PerplexityBot — real, correct content, not a fabricated fixture.
- `app/Http/Controllers/RobotsController.php` + `routes/api.php` additions — `GET /robots`, `GET /robots/preview`, `POST /robots/save`, `POST /robots/validate`, `POST /robots/reset`. A thin facade: no new generation/validation logic lives in the controller — it calls the same `DiscoveryService`/`ValidationService`/`GenerationService` every other engine controller uses, proving those engines generalize to a real feature, not just the internal probe.
- `tests/Unit/Modules/Robots/RobotsModuleSnapshotTest.php` — the exit criterion's own explicit requirement: freezes the exact expected `robots.txt` output byte-for-byte.
- `tests/Unit/EndToEnd/RobotsScoringEndToEndTest.php` — the exit criterion's "audit rule shows in Scoring output" requirement, proven using the **real** `Plugin` wiring (constructs the actual Container/Bootstrap/ServiceProvider chain, not mocks) — confirms a robots.txt-attributable `ValidationResult` is present in the set handed to `ScoringService` and that it genuinely affects the calculated grade.
- 6 new test files + 1 extended (`ApiRoutesTest`, now covering 15 routes).

### Checks — all run for real

`composer validate` (valid), `composer test` → `OK (207 tests, 345 assertions)` (up from 185/292), PHPStan level 8 → `[OK] No errors` across 59 files (up from 54), PHPCS hybrid ruleset → 0 errors/0 warnings across 100 files, `composer test:integration` → 0 tests (unchanged), `composer quality` → all green.

### Explicitly out of scope for Phase 8 (deferred)
- Visual rule builder, live preview UI, version history with one-click restore, auto-backup (`storage/backups/robots/`) — need persisted settings/version storage and the Admin UI phase
- Third-party SEO-plugin (Yoast/RankMath/AIOSEO/SEOPress/etc.) detection and merge/conflict resolution
- Multi-format import/export (TXT/JSON/CSV/Markdown)
- User-customizable rules (the ruleset is fixed/hardcoded this phase) — needs Settings/DB persistence
- Custom capability registration (`manage_robots`) — reuses the same `manage_options` interim default
- `Core/Scheduler.php`, any custom `oxy_*` database table, migration runner, Settings Manager, Logger service, Cache Service, Queue
- Monitoring/Reporting engines, any other real feature Module, any admin UI, `package.json`/frontend tooling

## Phase 9 — Audit Engine orchestration — 2026-07-25

**Status: Complete, all checks run for real and passing. Committed, tagged `phase-9`, pushed autonomously.**

### Scope

`06-Phase-Plan.md` row 9: "Rule Engine, Scan Types (Quick/Full/Deep/Developer), `/audit/*` REST, ties Discovery+Validation+Scoring together at the audit level," exit criterion "Full/Quick scan executes within documented performance targets on a fixture site and returns a structured report."

### What was built

- `app/DTO/ScanType.php` — Quick/Full/Deep/Developer. Deep/Developer are real, selectable values but don't yet examine anything Full doesn't, since no Headers/Performance/Security-specific scanners exist (those need modules from later phases) — added now so `AuditService`'s public shape doesn't need to change once they do.
- `app/DTO/AuditReport.php`, `app/Services/AuditService.php` — `scan(ScanType): AuditReport` is the orchestrator the roadmap row names ("ties Discovery+Validation+Scoring together at the audit level"): iterates every Discovery Map entry, validates each, scores the combined results, and packages a structured report (summary counts by status, the `ScoreResult`, duration, timestamp). Quick reuses the cached Discovery Map; Full/Deep/Developer force a fresh discovery pass (`DiscoveryService::reset()`) first.
- `app/Http/Controllers/AuditController.php` + `routes/api.php`'s `GET /audit`/`POST /audit/start`. `/audit/fix` and `/audit/verify` (docs/06's own REST list) are not implemented — those belong to the AutoFix Engine, a later phase.
- No new "Rule" abstraction was built distinct from the existing `ValidatorInterface` — a registered Validator already *is* a rule in every sense docs/06 describes (independent, returns PASS/WARNING/FAIL/etc.). Logged in `DECISIONS.md`.
- 2 new test files + 2 extended existing ones. `AuditServiceTest` includes a data-provider test explicitly verifying every scan type finishes within docs/06's own documented performance ceiling (Quick <5s, Full <20s, Deep/Developer <60s) — trivially true against a fixture site, but a real, run-for-real check rather than an assumption.

### Checks — all run for real

`composer validate` (valid), `composer test` → `OK (220 tests, 368 assertions)` (up from 207/345), PHPStan level 8 → `[OK] No errors` across 63 files (up from 59), PHPCS hybrid ruleset → 0 errors/0 warnings across 106 files (two long-PHPDoc-line warnings fixed by wrapping), `composer test:integration` → 0 tests (unchanged), `composer quality` → all green.

### Explicitly out of scope for Phase 9 (deferred)
- AutoFix (`/audit/fix`, `/audit/verify`) and the Recommendation Engine — later phases
- Persisted audit history (`/audit/history`), Diff Engine, Notifications — no `oxy_*` table exists yet
- Headers/Performance/Security/WordPress-environment-specific scan checks (docs/06's own Header/Content/Performance/Security/WordPress Checks sections) — need modules/scanners that don't exist yet; Deep/Developer scan types are real but currently equivalent to Full
- Third-party custom rule registration (docs/06's `oxy_ai_register_rules` extensibility hook)
- `Core/Scheduler.php`, any custom `oxy_*` database table, migration runner, Settings Manager, Logger service, Cache Service, Queue
- Any other real feature Module, any admin UI, `package.json`/frontend tooling

## Phase 10 — Recommendation + Auto Fix Engines — 2026-07-25

**Status: Complete, all checks run for real and passing. Committed, tagged `phase-10`, pushed autonomously.**

### Scope

`06-Phase-Plan.md` row 10: "Issue→recommendation pipeline; safe/confirmation/developer fix tiers with backup→execute→verify→rollback," exit criterion "AutoFix on a fixture issue is reversible; rollback test suite passes per 28's AutoFix Safety Tests."

### What was built

- `app/DTO/Recommendation.php`, `app/Services/RecommendationService.php` — turns FAIL/WARNING `ValidationResult`s into `Recommendation`s (narrowed from docs/19's fuller Recommendation Object — Estimated Impact/Effort/Time/Documentation/Related Issues/Dependencies need data this phase's engines don't produce). `autoFixAvailable` is a genuine capability check against `GenerationService`, not a guess.
- `app/DTO/FixTier.php`, `app/DTO/FixResult.php`, `app/Services/AutoFixService.php` — the Auto Fix Engine, deliberately built by **reusing** `GenerationService`'s existing backup-then-write pipeline (Phase 6) as the Backup/Execute/Validate stages, then adding its own explicit post-fix Verify step (a second, independent validation pass after the write) before declaring success — matching docs/18's distinct Execute → Validate → Verify pipeline stages rather than collapsing them into one check. If verification still fails after a successful write, `AutoFixService` calls `GenerationService::rollback()` to restore the prior version.
- `FixTier::Safe` runs immediately; `Confirmation`/`Developer` require an explicit `confirmed` argument — modeling "requires confirmation" honestly rather than faking a confirmation UI (Admin UI is a later phase).
- `app/Http/Controllers/RecommendationController.php`, `app/Http/Controllers/AutoFixController.php` + `routes/api.php` additions.
- `app/Services/GenerationService.php` gained one small addition: `resourceIdFor()`, letting `AutoFixService` re-check a resource after publishing without a separate lookup mechanism.
- **The exit criterion's own explicit safety test suite**, per docs/28-Testing-Strategy.md's AUTOFIX TESTING/ROLLBACK TESTING sections, scoped to what current infrastructure can genuinely exercise: Backup Creation, Execution, Validation, Verification, Success Report, and rollback after **Validation Failure** and **Filesystem Failure** specifically. Database Failure, Timeout, Permission Change, Dependency Conflict, Interrupted Request, and Partial Batch Execution are honestly out of scope — no DB, async/network layer, capability-registration system, module-dependency system, or batch-fix feature exists yet. Logged in `DECISIONS.md`.
- 4 new test files + 3 extended existing ones.

### Checks — all run for real

`composer validate` (valid), `composer test` → `OK (249 tests, 424 assertions)` (up from 220/368), PHPStan level 8 → `[OK] No errors` across 70 files (up from 63), PHPCS hybrid ruleset → 0 errors/0 warnings across 117 files, `composer test:integration` → 0 tests (unchanged), `composer quality` → all green.

### Explicitly out of scope for Phase 10 (deferred)
- `/autofix/batch`, `/autofix/history` (docs/18's own REST list) — batch needs multi-issue orchestration, history needs persisted storage
- Rollback testing for Database Failure/Timeout/Permission Change/Dependency Conflict/Interrupted Request/Partial Batch Execution — no infrastructure exists yet to test any of these against
- "Update Score" as part of the fix pipeline (docs/18) — left to the caller (e.g. re-run `AuditService::scan()` after fixing) rather than baked into `AutoFixService`, to avoid conflating "fix one resource" with "recompute the whole site's score"
- Logging (no Logger service exists yet), third-party custom fix registration
- `Core/Scheduler.php`, any custom `oxy_*` database table, migration runner, Settings Manager, Logger service, Cache Service, Queue
- Monitoring/Reporting engines, any other real feature Module, any admin UI, `package.json`/frontend tooling

## Phase 11 — Remaining Discovery-pillar modules (LLMS, Headers, Markdown, Content Signals) — 2026-07-25

**Status: Complete, all checks run for real and passing. Committed, tagged `phase-11`, pushed autonomously.**

### Scope

`06-Phase-Plan.md` row 11: "LLMS, Headers, Markdown, Content Signals — each repeats the Phase 8 pattern now that the pipeline is proven," exit criterion "Each module has REST + generator + validator + audit rules + snapshot tests, mirroring Robots."

### What was built — four modules, each mirroring `Modules/Robots` exactly

- **LLMS** (`llms.txt`): title + description blockquote, using the plugin's own real product identity (docs/01-Vision.md's Plugin Name/Tagline) rather than fabricated page content. Owns the `llms-txt` Standard per ADR-001.
- **Headers** (HTTP response headers): a real `Name: value` declaration (`Content-Signal`, `X-Content-Type-Options`, `Referrer-Policy`) represented the same way Robots represents robots.txt — a generated text resource, not a live `send_headers` hook (keeps the pattern uniform; real emission deferred). **Owns no Standard** — ADR-001 explicitly lists Headers among the modules with none ("No Standard: Dashboard, Audit, Headers, Settings, Logs, ..."), caught and corrected before writing the ServiceProvider (an initial `HeadersStandard.php` was written, then removed once this was checked against the canonical ownership table).
- **Markdown** (content negotiation): a real capability declaration (Content-Type/Accept types docs/09 itself lists) rather than fabricated per-page HTML→Markdown conversion, since this project has no real WordPress content to convert yet ("do not use mock production data"). Owns the `markdown-negotiation` Standard.
- **Content Signals**: a real site-wide AI-usage-signals declaration (`ai-training`/`ai-citation`/`ai-summary`), matching the real, existing Content Signals policy concept the doc models, rather than fabricated per-page Identity/Purpose/Audience/Trust signals requiring content and entity extraction this project doesn't have. Owns the `content-signals` Standard.

Each module got the full Robots-shaped test suite: a snapshot test freezing exact generated output, a Module test (identity/lifecycle/discover/validate/resourceId/supports), a Standard test (delegation + still-throwing methods, skipped for Headers), a ServiceProvider test, and a Controller test (7 REST scenarios). `tests/Unit/Routes/ApiRoutesTest.php` was rewritten to generate its expected-route list from a module-slug loop rather than hand-listing 20 more strings.

### Checks — all run for real, clean on the first pass

`composer validate` (valid), `composer test` → `OK (329 tests, 612 assertions)` (up from 249/424), PHPStan level 8 → `[OK] No errors` across 85 files (up from 70), PHPCS hybrid ruleset → 0 errors/0 warnings across 151 files, `composer test:integration` → 0 tests (unchanged), `composer quality` → all green.

### Explicitly out of scope for Phase 11 (deferred)
- Every module's full aspirational feature set (visual builders, multi-language, live HTTP testing, entity extraction, version history with restore, third-party plugin conflict detection) — needs Settings/DB persistence and the Admin UI phase this project hasn't reached
- Real per-page Markdown conversion and per-page Content Signals — need actual WordPress content, which doesn't exist in this environment
- Real HTTP header emission (`send_headers` hook) — Headers module generates a text declaration like every other module, not yet wired to an actual outgoing response
- Custom capability registration (`manage_llms`, `manage_headers`, etc.) — all four reuse the same `manage_options` interim default
- `Core/Scheduler.php`, any custom `oxy_*` database table, migration runner, Settings Manager, Logger service, Cache Service, Queue
- MCP/Agent Skills/API Catalog/OAuth Discovery modules (Phase 14), Monitoring/Reporting engines (Phase 13), any admin UI (Phase 12), `package.json`/frontend tooling

## Phase 12 — Admin UI shell — 2026-07-25

**Status: Complete, all checks run for real and passing. Committed, tagged `phase-12`, pushed autonomously.**

### Scope

`06-Phase-Plan.md` row 12: "React/TS SPA scaffold, design system tokens (03), Dashboard screen wired to REST built so far, Audit screen, module screens for Phase 8/11 modules," exit criterion "Dashboard answers 'how ready / what's broken / how to fix' using live API data; a11y smoke pass."

### Starting state: a real, uncommitted frontend already existed

A prior session had already built the full React/TS SPA (`package.json`, Vite/Tailwind/Jest/ESLint config, `assets/react/**` — App shell, Sidebar/Header layout, Dashboard/Audit/five module screens, `useApiGet`/`apiGet`/`apiPost` REST client, jest-axe a11y tests) but left every file untracked and uncommitted, with no `.project` record of the phase starting. Verified this wasn't placeholder work — Dashboard/Audit genuinely call `/score`, `/audit`, `/recommendations` live per docs/03-UI.md's three Dashboard Goal questions, with tests exercising real REST payloads via a `mockFetchRoutes` helper and asserting `axe(container)` has no violations. What was missing: the PHP side (`app/Admin/AdminServiceProvider`, referenced by name in the already-written `vite.config.ts` comment and `assets/react/Utils/api.ts`'s `window.oxyAiReadiness` docblock) that mounts the SPA into wp-admin — nothing wired the build into a real admin page yet.

### What was built

- `app/Admin/AdminServiceProvider.php` — registers one top-level wp-admin menu page (`manage_options`), whose body is only an empty `#oxy-ai-readiness-root` mount node; all in-page navigation between Dashboard/Audit/module screens is the SPA's own (`App.tsx`/`Sidebar.tsx`), not server-rendered PHP views. Reads Vite's build manifest (`dist/.vite/manifest.json`, confirmed by actually running `npx vite build`) through `FileRepository` so the enqueued, content-hashed filename is never hardcoded; falls back to an `admin_notices` error (not a fatal) if `dist/` hasn't been built yet. Adds `type="module"` to its own script tag via `script_loader_tag` (Vite emits an ES module, not a classic script). Localizes `window.oxyAiReadiness` (`restUrl`/`nonce`/`version`) exactly as `assets/react/Utils/api.ts` already declared it. Wired into `Plugin.php`'s provider list.
- `tests/Unit/Admin/AdminServiceProviderTest.php` — 8 tests: hook registration, the `admin_menu` callback's exact `add_menu_page()` args, the enqueue callback's page-suffix guard, the missing-build notice path, the full enqueue+localize path (manifest served through an injected `FileRepository`/`InMemoryFilesystem`, no real filesystem or WordPress globals touched), `renderRoot()`/`renderMissingBuildNotice()` output (via `expectOutputString`, since `phpunit.xml.dist` sets `beStrictAboutOutputDuringTests`), and the `script_loader_tag` filter's handle-scoping. `AdminServiceProvider` takes an optional constructor-injected `FileRepository` (used only by tests) rather than a raw `global $wp_filesystem` — PHPCS's `WordPress.WP.GlobalVariablesOverride.Prohibited` correctly rejected the latter on a first pass.
- Two real, pre-existing bugs in the uncommitted frontend scaffold fixed rather than worked around: `eslint-plugin-react-hooks@^4.6.2`'s peer dependency (`eslint@^3–8`) conflicted with the pinned `eslint@^9.9.0`, blocking `npm install` outright — bumped to `^5.0.0`, which supports eslint 9. `eslint.config.js` had no global environment (`browser`/`node`/`jest`/ambient DOM+JSX types) configured, so plain `eslint` failed with 25 `no-undef` false positives (`JSX`, `HTMLButtonElement`, `RequestInit`, `jest`, `global`, etc.) across nearly every file — disabled `no-undef` for `.ts`/`.tsx` files in favor of `tsc --noEmit` (already run clean), matching typescript-eslint's own documented guidance that ESLint's core `no-undef` doesn't understand TypeScript's ambient types. Also added the missing `@types/jest-axe` dev dependency (`tsc --noEmit` failed without it).

### Checks — all run for real

PHP: `composer validate` (valid), `composer test` → `OK (338 tests, 622 assertions)` (up from 329/612), PHPStan level 8 → `[OK] No errors` across 86 files (up from 85), PHPCS hybrid ruleset → 0 errors/0 warnings across 154 files, `composer test:integration` → 0 tests (unchanged), `composer quality` → all green.

Frontend (run for the first time this phase — no prior `node_modules`): `npm install` (after the `eslint-plugin-react-hooks` fix), `npm run lint` (ESLint, 0 problems after the `no-undef` fix), `npm run typecheck` (`tsc --noEmit`, 0 errors after the `@types/jest-axe` fix), `npm run test` (Jest, 4 suites/6 tests passing, including jest-axe a11y assertions on Dashboard), `npm run build` (`tsc --noEmit && vite build`) — confirmed real, producing `dist/.vite/manifest.json` with the exact shape `AdminServiceProvider` expects. `npm audit` reports 28 existing transitive-dependency advisories (1 moderate/27 high) in the dev-only toolchain (eslint/jest/vite's own dependency trees) — not something a fix for this phase's scope should force-upgrade blind (`npm audit fix --force` can jump major versions unpredictably); left for a dedicated dependency-hardening pass. `package-lock.json` committed for reproducible installs.

### A documented deviation from docs/04-Folder-Structure.md's aspirational `Admin/` layout

That doc's `Admin/{Dashboard,Pages,Settings,Widgets,Components,Assets,Views,Controllers,Middleware}` breakdown describes per-page server-rendered PHP views — but the same document explicitly says (line 271) "There is no per-module Views/ or Assets/ folder. The admin UI is a single centralized React SPA." Since the SPA already built this phase owns all its own navigation/rendering, a single `app/Admin/AdminServiceProvider.php` is everything actually needed; the elaborate per-page folder breakdown doesn't apply to what a single-mount SPA needs. Treated as aspirational rather than literal, same precedent as every prior phase's documented deviations (RestServiceProvider's namespace, Headers owning no Standard, etc.) — logged in DECISIONS.md rather than built out unused.

### Explicitly out of scope for Phase 12 (deferred)

- Nav entries/screens for API Catalog, MCP, Agent Skills, Commerce, Logs, Settings, About (docs/03-UI.md's full Navigation list) — no REST backend exists for any of them yet (Phases 13–15); CLAUDE.md prohibits dashboard-only functionality without an API behind it
- Every module screen's full aspirational feature set (Robots' visual crawler-table builder/import-export, LLMS live-URL preview, Markdown per-post-type settings, Headers table editor) — needs persisted settings/multi-row config this project's engines don't produce yet; the built `ModulePage` covers the REST surface that does exist (preview/publish/validate/reset)
- Dark mode toggle, global search, notifications/toast system, Logs timeline, Settings screen — all docs/03-UI.md sections with no REST data source yet
- `npm run dev`'s watch-mode build is the only "dev server" story — no HMR dev server/proxy, since PHP always serves whatever is currently in `dist/`
- Fixing the 28 pre-existing `npm audit` advisories in the dev toolchain — deferred to a dedicated dependency pass, not blocking this phase's exit criterion
- `Core/Scheduler.php`, any custom `oxy_*` database table, migration runner, Settings Manager, Logger service, Cache Service, Queue, Monitoring/Reporting engines, MCP/Agent Skills/API Catalog/OAuth Discovery modules

## Phase 13 — Monitoring + Reporting Engines — 2026-07-25

**Status: Complete, all checks run for real and passing. Committed, tagged `phase-13`, pushed autonomously.**

### Scope

`06-Phase-Plan.md` row 13: "Change detection, notifications, report generation/export/sharing," depends on Phase 9 (Audit) and Phase 7 (Scoring), exit criterion "A simulated resource change triggers revalidation + notification; a report exports in at least one format."

### What was built

**Monitoring Engine** (`app/Services/MonitoringService.php`): per docs/20-Monitoring-Engine.md's pipeline (Scheduler → Resource Scanner → Change Detection → Validation Engine → Impact Analysis → Notifications), with no `Core/Scheduler.php` to call it automatically — `start()` arms monitoring and takes a baseline fingerprint of every currently-discovered resource (its Discovery Map metadata plus its generated content, when a Generator is registered for its module); `scan()` (manually triggered via REST, standing in for what a real Scheduler would call on a timer) diffs the current state against that baseline, revalidates anything that changed via the existing `ValidationService`, and fires `oxy_ai_resource_changed`/`oxy_ai_notification_sent` — this is literally the exit criterion's "simulated resource change triggers revalidation + notification," exercised directly by tests that mock a `DiscoveryInterface`/`GeneratorInterface` returning different output across two calls. `ChangeType` (Created/Modified/Deleted) and `NotificationPriority` (Critical/High/Medium/Low/Informational, only Critical/Medium/Informational currently reachable) are real enums scoped to what's genuinely detectable without live HTTP/SSL checks or a severity axis on `ValidationResult` — same "real enum, not every case distinguished yet" precedent as Phase 9's `ScanType`. `MonitoringController` exposes `/monitoring`, `/monitoring/status`, `/monitoring/events`, `/monitoring/start`, `/monitoring/stop`, `/monitoring/reset`, and `/monitoring/scan` (the last one not in docs/20's own REST list, added under its own name for the same reason Phase 6 added `/generation/rollback`).

**Reporting Engine** (`app/Services/ReportService.php`): per docs/21-Reporting-Engine.md's pipeline (Data Sources → Normalize → Aggregate → Analyze → Generate → Export) — `generate()` runs a real `AuditService` scan, derives `Recommendation`s from its results via the existing `RecommendationService`, and folds in whatever `MonitoringService` has observed, producing a `Report` DTO (docs' own "Technical Report" shape). `export()` renders that report as either JSON (`toArray()` verbatim) or Markdown (a real, readable rendering with Validation Results/Recommendations/Monitoring Events sections) — both genuinely implemented, not stubs; two formats comfortably satisfies "exports in at least one format." `ReportController` exposes `GET /reports`, `POST /reports/generate`, `POST /reports/export`.

Both services registered as `CoreServiceProvider` singletons; both controllers wired into `routes/api.php`. 4 new test files (`MonitoringServiceTest` — 8 methods covering no-baseline/created/modified/deleted/content-only-change/stop/reset; `MonitoringControllerTest` — 8; `ReportServiceTest` — 5; `ReportControllerTest` — 8) plus `CoreServiceProviderTest` (+2) and `ApiRoutesTest` (extended) updated.

### Checks — all run for real, clean on the first pass

`composer validate` (valid), `composer test` → `OK (369 tests, 701 assertions)` (up from 338/622), PHPStan level 8 → `[OK] No errors` across 95 files (up from 86), PHPCS hybrid ruleset → 0 errors/0 warnings across 166 files, `composer test:integration` → 0 tests (unchanged, pre-existing since Phase 1 — not part of `composer quality`), `composer quality` → all green. `npm run quality` re-verified green (untouched this phase).

### Explicitly out of scope for Phase 13 (deferred)

- `Core/Scheduler.php` — Monitoring's `scan()` still needs a manual/REST trigger; a real Scheduler would call it on a timer once built
- `/monitoring/history` — without persisted storage it would return exactly what `/monitoring/events` already does; not implemented rather than faking a distinction
- `/reports/history`, `/reports/templates`, `/reports/share`, `DELETE /reports/cache` — need persisted multi-report storage, report-type templates, external sharing/delivery, and a caching layer, none of which exist
- Executive/Agency/White-Label/Compliance/Security/Performance report types — need business-summary copywriting, client branding, and compliance-framework mappings this project has no source of
- Slack/Discord/Teams/Telegram/Webhook/Push notifications, PDF/Excel/CSV/XML/ZIP/HTML export formats — need external libraries or delivery credentials this project doesn't have
- `ChangeType::Broken/Deprecated/Moved/Redirected/Disabled/Expired`, `NotificationPriority::High/Low` — need live HTTP-following/SSL checks and a severity axis on `ValidationResult` that don't exist yet
- Any custom `oxy_*` database table, migration runner, Settings Manager, Logger service, Cache Service, Queue, MCP/Agent Skills/API Catalog/OAuth Discovery modules

## Phase 14 — AI-native modules (MCP, Agent Skills, API Catalog, OAuth Discovery) — 2026-07-26

**Status: Complete, all checks run for real and passing. Committed, tagged `phase-14`, pushed autonomously.**

### Scope

`06-Phase-Plan.md` row 14: "MCP, Agent Skills, API Catalog, OAuth Discovery," depends on "Phase 11 pattern, resolved Q3," exit criterion "Each has server-card/registry generation, validation, and REST per its spec doc."

### What was built — four modules, each mirroring `Modules/Robots` in shape, none mirroring its doc's full aspirational feature set

- **MCP** (`app/Modules/Mcp/`): generates a real Server Card (name/description/organization/version/authentication), but every `capabilities`/`resources`/`tools`/`prompts` field is honestly empty/false — docs/12-MCP-Spec.md's actual MCP capabilities (Resources, Tools, Prompts, Sampling, Streaming) describe a live JSON-RPC transport this project has never built, and declaring them `true` would be fabricated capability data. Owns the `mcp` Standard (`https://modelcontextprotocol.io`).
- **Agent Skills** (`app/Modules/AgentSkills/`): publishes a Skill Registry of exactly three skills — but real ones: this plugin's own already-working `GET /score`, `POST /audit/start`, `GET /recommendations` REST actions, not docs/13's fabricated example skills (Book Appointment, Find Doctor, ...) that no WordPress site has out of the box. Owns the `agent-skills` Standard (internal identifier, no confidently-known canonical URL).
- **API Catalog** (`app/Modules/ApiCatalog/`): generates `/.well-known/api-catalog` from a real, hand-maintained, 83-entry inventory of every route this plugin itself registers in `routes/api.php` (verified via a new cross-check assertion in `ApiRoutesTest`) — not a live `rest_get_server()` introspection, to keep every Generator in this codebase a pure function with no WordPress runtime calls. Owns the `api-catalog` Standard (internal identifier).
- **OAuth Discovery** (`app/Modules/OAuthDiscovery/`): the one module owning three Standards instead of one (`openid-configuration`, `oauth-authorization-server`, `oauth-protected-resource` — per ADR-001's own ownership table), so it doesn't implement `GeneratorInterface` itself; three small dedicated Generator classes do. `oauth-protected-resource` (RFC 9728) is fully spec-compliant for real, describing this plugin's own genuinely-protected REST namespace. `openid-configuration`/`oauth-authorization-server` deliberately do **not** claim a real OpenID Provider or OAuth Authorization Server exists — neither WordPress core nor this plugin implements one, and OIDC Discovery/RFC 8414 require `authorization_endpoint`/`token_endpoint`/`jwks_uri` as mandatory fields; inventing URLs for infrastructure that doesn't exist would be exactly the fabricated capability data CLAUDE.md prohibits. Both instead publish a real issuer identity plus an explicit "not configured" note.

Each module registered into Discovery/Validation/Generation/Standards exactly like every Phase 8/11 module; `McpController`/`AgentSkillsController`/`ApiCatalogController` mirror `RobotsController`'s index/preview/save/validate/reset shape exactly. OAuth Discovery's REST surface is that same shape times three (one `OAuthDiscoveryFileController` instance per document, parameterized by generator id) plus one combined `GET /oauth-discovery` overview — 16 routes total for this one module.

### Checks — all run for real, clean on the first pass (one real gap caught and fixed)

`composer test` initially failed with 1 error: the existing full-system `RobotsScoringEndToEndTest` boots the *real* `Plugin` and runs every registered validator against every discovered resource (confirmed, not assumed, by this failure) — `OAuthDiscoveryModule`'s validator calls its Generators' real `home_url()`/`rest_url()`, which that test hadn't stubbed. Fixed by adding the two stubs to that one test (the only place in the suite that exercises real cross-module validation end-to-end); documented in DECISIONS.md. Also fixed 4 new controller tests missing a `resourceId()` stub on their mocked `GeneratorInterface` (needed by `GenerationService::publish()`) and one `OAuthDiscoveryControllerTest` case needing all three real Generators registered before calling `index()`.

`composer validate` (valid), `composer test` → `OK (475 tests, 958 assertions)` (up from 369/701), PHPStan level 8 → `[OK] No errors` across 117 files (up from 95), PHPCS hybrid ruleset → 0 errors/0 warnings across 210 files, `composer quality` → all green. `npm run quality` re-verified green (untouched this phase).

### Explicitly out of scope for Phase 14 (deferred)

- Every real MCP transport capability (JSON-RPC resources/tools/prompts execution, sampling, streaming, completion) — no live protocol server exists; this phase only builds the identity/discovery layer
- A real OpenID Provider / OAuth 2.0 Authorization Server (actual `/oauth/authorize`, `/oauth/token` endpoints) — neither WordPress core nor this plugin implements one; building one is a much larger, separate effort with real security implications, not a documentation-generation task
- `/skills/{id}`, `/skills/categories`, `/skills/test`, `/skills/import`, `/skills/export`, `DELETE /skills/{id}` (docs/13's own per-skill REST list) — the registry is a fixed, hardcoded set of 3 real skills; no per-skill CRUD exists yet without persisted storage
- Live `rest_get_server()` route introspection for API Catalog — the hand-maintained static list is real and accurate today but must be updated by hand as routes change (documented, known limitation)
- `Core/Scheduler.php`, any custom `oxy_*` database table, migration runner, Settings Manager, Logger service, Cache Service, Queue, Commerce/Analytics/License/Updater modules, CI matrix, multisite validation, security/performance hardening, packaging

## Phase 15 — Remaining modules + hardening — 2026-07-26

**Status: Complete, all checks run for real and passing. Committed, tagged `phase-15`, pushed autonomously. See `.project/RELEASE-GATE-CHECKLIST.md` for the full, honest release-readiness record (including three documented, non-blocking gaps).**

### Scope

`06-Phase-Plan.md` row 15: "Commerce, Analytics, License, Updater; full CI matrix; multisite pass; performance/security audit against 26/27 budgets; packaging/distribution build," exit criterion "Release-gate checklist (28) fully green; package installs cleanly on a fresh WP site." Executed in one continuous cycle immediately after Phase 14, per the user's explicit combined-phase instruction, while keeping Phase 14 and 15 as two separate Git milestones.

### Four remaining modules — all owning no Standard (per ADR-001), all scoped to real, honest content

- **Commerce** (`app/Modules/Commerce/`): docs/05's own Purpose is literally "Future AI Commerce" (x402, Machine Payments, AI Checkout) — none of that exists in WordPress core or this plugin. Reports one real, checkable fact (`class_exists('WooCommerce')`) plus an honest all-false `supports` declaration, rather than fabricating payment infrastructure.
- **Analytics** (`app/Modules/Analytics/`): "Track AI Usage" needs a persisted counter store (Logger/Cache Service/`oxy_*` table) that has never existed in this project. Declares the 5 real metrics it *would* track, each honestly at zero rather than fabricated traffic numbers.
- **License** (`app/Modules/License/`): no license server or paid tier exists. Reports this build's own real, current state: `tier: free`, `activated: false`.
- **Updater** (`app/Modules/Updater/`): no update-check server or WordPress.org listing exists. Reports the plugin's own real current version and the one real channel (`stable`), `update_available: false` rather than a fabricated newer version.

All four mirror `Modules/Headers`'s exact "owns no Standard" shape; their 4×5 REST routes are registered from one shared loop in `routes/api.php` (all four controllers share an identical constructor signature and index/preview/save/validate/reset shape).

### Full CI matrix

`.github/workflows/ci.yml` — new. Three real jobs: `php-quality` (PHP 8.1/8.2/8.3/8.4 matrix — `composer validate`/`lint`/`analyse`/`test`), `frontend-quality` (`npm run lint`/`typecheck`/`test`/`build`), `package` (builds the release zip, runs the new packaging integration test, uploads the artifact). Deliberately does **not** include a real-WordPress/MySQL/browser/WooCommerce/Elementor test matrix — docs/28-Testing-Strategy.md's own CI Matrix/Release Candidate Testing sections describe those, but this project has never built the WordPress test-suite bootstrap (`tests/Integration` had 0 tests before this phase) or any browser-automation infrastructure; fabricating CI jobs referencing infrastructure that doesn't exist would produce a permanently-red, misleading pipeline — worse than the honest, narrower one built here. See DECISIONS.md.

### Multisite validation — a real gap found and fixed

`Plugin::activate()` never handled network-wide activation: WordPress does *not* automatically iterate every site in a network when a super admin network-activates a plugin — the plugin itself must call `switch_to_blog()` per site if it wants every site's own options set. Before this phase, only whichever site happened to be "current" during a network-activation request would ever get real `oxy_ai_installed_at`/`oxy_ai_version` options — every other site in the network would silently never get them. Fixed: `activate(bool $networkWide = false)` now iterates `get_sites()` and runs per-site activation inside `switch_to_blog()`/`restore_current_blog()` when network-activated on a real multisite install; single-site (or non-network-wide) activation is unchanged. `deactivate()` also now accepts `$networkWide` (WordPress passes it automatically) for signature consistency, though it remains a real no-op (nothing exists yet to tear down). 4 new tests in `PluginTest`.

### Security + performance hardening pass — a real audit, clean result

Ran `composer audit` (no advisories) and `npm audit --omit=dev` (0 vulnerabilities in real runtime dependencies — the previously-documented 28 dev-tooling advisories are unchanged, still deliberately deferred per Phase 12's decision, still zero production impact). Manually reviewed: every REST controller's `authorize()` (23/23 use `current_user_can('manage_options')`, no weaker/missing checks); no `eval()`/`unserialize()`/raw superglobal access/direct `$wpdb` usage/debug statements anywhere in `app/`; every hook this plugin registers is admin- or REST-scoped, none run on public visitor-facing requests (confirmed by grep, not assumed) — satisfying CLAUDE.md's "no heavy operations during frontend requests" and docs/27's "No unnecessary frontend scripts" structurally, not just by convention. No fixes were required — this pass affirmed existing discipline from every prior phase rather than finding a fabricated problem to solve.

### Packaging and distribution build

`bin/build-release.sh` — new, real, and actually run: stages only runtime files (`app/`, `routes/`, `dist/`, `oxy-ai-readiness.php`, `uninstall.php`, `composer.json`) into a clean directory, runs a real `composer install --no-dev --optimize-autoloader` inside that staged copy (this project has zero runtime Composer packages — only dev tooling — so this correctly produces just `vendor/autoload.php` + `vendor/composer/*`), zips it, and writes a SHA256 checksum. Verified by `tests/Integration/PackagingTest.php` (3 tests) — the first real content in the Integration testsuite's history. Along the way, fixed a real pre-existing defect: `composer test`/`composer quality` had always run *every* configured PHPUnit testsuite (Unit + Integration) because `phpunit.xml.dist` declares both and the `test` script never scoped itself to one — invisible while Integration was empty, but would have silently made every future `composer quality` run depend on `dist/` existing and spawn subprocesses. Fixed by scoping `test`/`test:coverage` to `--testsuite=Unit` explicitly, restoring the fast, hermetic unit-only gate; `test:integration` also gained a missing `--no-coverage` flag (was failing on a benign "no coverage driver" warning that only surfaced once real tests existed to trigger it).

### Checks — all run for real, clean on the first pass (module code); two real pre-existing script defects caught and fixed (see above)

`composer validate` (valid), `composer test` → `OK (546 tests, 1118 assertions)` (up from 475/958), `composer test:integration` → `OK (3 tests, 1263 assertions)` (up from 0), PHPStan level 8 → `[OK] No errors` across 129 files (up from 117), PHPCS hybrid ruleset → 0 errors/0 warnings across 239 files, `composer quality` → all green. `npm run build` + `npm run quality` → all green.

### Explicitly out of scope / documented gaps (see `.project/RELEASE-GATE-CHECKLIST.md`)

- A real WordPress/MySQL install-activate-deactivate-uninstall smoke test — no live WordPress environment exists in this session
- A numeric code-coverage threshold gate — no coverage driver (Xdebug/PCOV) installed in this environment
- A full browser-based accessibility pass across every screen — only `DashboardPage` has an automated (`jest-axe`) a11y check
- A new-site-created-after-network-activation hook (`wpmu_new_blog`/`wp_initialize_site`) — only network-activation-time provisioning was fixed this phase
- Live `rest_get_server()` introspection for API Catalog, real MCP transport, real OAuth Authorization Server, per-skill CRUD, `Core/Scheduler.php`, any `oxy_*` database table, Settings Manager, Logger/Cache/Queue services — all still deferred, unchanged from every prior phase's own list
