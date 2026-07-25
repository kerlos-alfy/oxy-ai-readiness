# Phase 2 Report — Foundational Scaffolding

**Date:** 2026-07-25.
**Status:** Complete, validated, and approved by the user. Phase 3 follows.

## 0. Scope resolution — read this first

The draft `06-Phase-Plan.md` had its own Phase 1 = "Foundational Scaffolding" (bootstrap file, Service Container, `Core/`) and Phase 2 = "Database & shared infrastructure" (migration runner, `oxy_settings`/`oxy_modules`/`oxy_standards` tables, Settings Manager, Logger, Cache Service, Queue skeleton). The user's actual, approved Phase 1 was narrowed to only the Repository layer (`PHASE-1-REPORT.md`), which left the draft's Phase 2 with an unmet prerequisite: no Container or bootstrap existed for a migration runner or Settings Manager to register into, per `docs/02-Architecture.md`'s Bootstrap Sequence (Autoloader → Constants → **Service Container** → Register Services → **Core Components** → Modules → Hooks → REST → Admin).

This was surfaced to the user directly (`AskUserQuestion`) before writing any code, rather than assuming either interpretation. The user chose to scope this Phase 2 as exactly the deferred Foundational Scaffolding, explicitly excluding database tables, migrations, Settings Manager, Logger, Cache Service, Queue, and any business logic — those move to a future phase.

## 1. Scope

Per the approved plan: plugin bootstrap file, `uninstall.php`, `readme.txt`, a minimal Service Container, `Core/` (Application, Bootstrap, Kernel, Config, Hooks — **not** Scheduler/ModuleRegistry/StandardsRegistry, which belong to Queue infra and the Module/Standard SDK respectively, later phases), and a base `ServiceProvider` class. No Modules, no REST, no database tables/migrations, no Settings Manager/Logger/Cache Service/Queue, no admin UI.

## 2. What was implemented

### Root (WordPress plugin packaging)
| File | Purpose |
|---|---|
| `oxy-ai-readiness.php` | Plugin header (name, description, version 0.1.0, requires WP 6.5+/PHP 8.1+, text domain), a PHP-version guard that shows an admin notice and returns early if PHP < 8.1 (before the autoloader is even required), then constructs `Core\Plugin` and registers activation/deactivation hooks |
| `uninstall.php` | `WP_UNINSTALL_PLUGIN` guard only — no cleanup logic invented for state that doesn't exist yet |
| `readme.txt` | WP-standard plugin readme header + changelog |

### `app/Core/`
| File | Responsibility |
|---|---|
| `Container.php` | Minimal DI container: `bind()`, `singleton()`, `make()`, `has()`. Zero-argument factories by design — no auto-wiring built speculatively ahead of a real consumer needing it |
| `Application.php` | Holds the `Container`, tracks boot state, thin pass-through methods |
| `Config.php` | Plugin-level metadata only (version, plugin file/dir, text domain) — not the module-level `config/*.php` files, none of which have an owning module yet |
| `Hooks.php` | Registrar wrapping `add_action`/`add_filter` with bookkeeping, so later Providers register hooks declaratively through one testable object |
| `Bootstrap.php` | Idempotent boot sequence: marks the `Application` booted, fires a new `oxy_ai_ready` action (the documented "Plugin Ready" step) |
| `Kernel.php` | Registers the plugin on WordPress's `plugins_loaded`, delegates to `Bootstrap` when fired — the only class that decides *when* the plugin boots |
| `Plugin.php` | Top-level object instantiated by the bootstrap file; wires Container → Application → Bootstrap → Kernel; `activate()` genuinely uses Phase 1's `OptionsRepository` for `installed_at`/`version` (its own documented use case); `deactivate()` is a real, intentionally empty callback |

### `app/Providers/`
| File | Responsibility |
|---|---|
| `ServiceProvider.php` | Abstract base class (`register()`/`boot()`) every future Core/Module provider extends |

Explicitly **not** built this phase: `Core/Scheduler.php`, `Core/ModuleRegistry.php`, `Core/StandardsRegistry.php` — Queue/Scheduler infra and the Module/Standard SDK are later phases, not scaffolding.

### Tests
8 new test files, 24 new test methods: `ContainerTest` (5), `ApplicationTest` (3), `ConfigTest` (3), `HooksTest` (2), `BootstrapTest` (2), `KernelTest` (2), `PluginTest` (5), `ServiceProviderTest` (2).

## 3. Checks performed — all run for real

Same canonical PHP (`C:\php\php.exe`) as Phase 1's validated environment, no new environment blockers this phase.

- `composer validate` → valid.
- `composer test` (`phpunit --testsuite=Unit --no-coverage`) → `OK (78 tests, 89 assertions)` — 54 from Phase 1 + 24 new.
- `composer test:integration` → 0 tests (unchanged from Phase 1 — still none written).
- PHPStan level 8, now also scoped to the two new root plugin files → `[OK] No errors`.
- PHPCS (hybrid ruleset, now also linting `oxy-ai-readiness.php`/`uninstall.php`) → 0 errors, 0 warnings across 33 files.
- `composer quality` → all green.

Three tests initially came back PHPUnit-"risky" (no native assertions — only Mockery/Brain-Monkey expectations verified in `tearDown()`). Fixed by asserting against Brain Monkey's real simulated hook storage where one exists (`Actions\has('plugins_loaded')`) or by calling PHPUnit's own `expectNotToPerformAssertions()` where only a Mockery expectation applies (`get_option`/`update_option`, which Brain Monkey does not simulate for real) — not by adding a hollow `assertTrue(true)`.

**Known gap, flagged not hidden (same as Phase 1's coverage-driver gap):** "plugin activates cleanly on a clean WordPress install" — the original draft's own exit criterion for this scaffolding — still cannot be verified here. There is no real WordPress install in this sandbox, only Brain Monkey's simulated hook functions. Everything unit-testable was verified for real; the true activation smoke test stays an open risk until run against an actual WP instance.

## 4. Documentation updates

None to `docs/*` this phase — implemented against the already-canonical docs without discovering new doc-level conflicts (the scaffolding/DB-infra scope gap was a phase-plan sequencing issue, not a `docs/` conflict, and was resolved with the user directly rather than by editing `docs/`).

## 5. Decisions requiring your attention

Full detail in `.project/DECISIONS.md`. The one most likely to need a second look:

- **`ServiceProvider` lives at `app/Providers/ServiceProvider.php`, not the `OxyAI\Core\Container\ServiceProvider` namespace `docs/29-Developer-Guide.md`'s worked example literally imports** — that import would require `Container` to be a folder/namespace, conflicting with `docs/04-Folder-Structure.md`'s flat `Core/Container.php` file. Treated the example as illustrative (same precedent as Phase 1's `RepositoryInterface` decision) and used the already-documented top-level `Providers/` folder instead. If you intended the literal nested namespace, let me know before any Provider code depends on this path.
- A new hook name, `oxy_ai_ready`, was introduced for the "Plugin Ready" bootstrap step since no hook-naming convention exists in `docs/*` yet — reused the `oxy_ai_` prefix `OptionsRepository` already established rather than inventing an unrelated one.

## 6. Files created/modified this phase

11 new files (3 root plugin files, 8 `app/` source files) + 8 new test files + 2 modified tooling configs (`phpcs.xml`, `phpstan.neon`, to cover the new root files) + `.project/PROGRESS.md`, `.project/DECISIONS.md`, `.project/FILE-MANIFEST.md` updated, this report new. Full manifest in `.project/FILE-MANIFEST.md`.

## 7. What's explicitly still missing (by design — later phases)

`Core/Scheduler.php`, `Core/ModuleRegistry.php`, `Core/StandardsRegistry.php`, any custom `oxy_*` database table or migration, Settings Manager, Logger service, Cache Service, Queue, any Module, any Standard, any REST endpoint, any admin UI, `package.json`/frontend tooling.

## 8. Recommendation for Phase 3

With Foundational Scaffolding now in place, Phase 3 is well-positioned to be either (a) the "Database & shared infrastructure" work originally drafted as Phase 2 — migration runner, `oxy_settings`/`oxy_modules`/`oxy_standards` tables, Settings Manager, Logger, Cache Service, Queue skeleton, now that there's a Container/Bootstrap for them to register into — or (b) the "Module & Standard SDK skeleton" (the draft's Phase 3). I'll wait for your explicit scope instruction rather than assume, same as after Phase 1.

---

**Approved by the user. Proceeding to Phase 3.**
