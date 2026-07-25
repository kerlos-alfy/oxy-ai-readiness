# Phase 3 Report — Module & Standard SDK Skeleton

**Date:** 2026-07-25.
**Status:** Complete, validated, and approved by the user. Awaiting scope instruction for Phase 4.

## 0. Scope resolution — read this first

The user's instruction was "start Phase 3 exactly according to the roadmap." Read against `06-Phase-Plan.md`'s own numbering, row 3 is "Module & Standard SDK skeleton" (`ModuleInterface`, `StandardInterface`, Module Registry, Standards Registry, `ServiceProvider` pattern, module lifecycle wiring, one minimal internal "probe" module/standard for validation only, not user-facing) — listed as depending on the draft's own row 2 ("Database & shared infrastructure"), which still does not exist under our actual phase numbering (our Phase 2 was the deferred scaffolding, the draft's row 1).

Proceeded with row 3 anyway rather than asking again, because:
1. The user named "Phase 3" specifically, which resolves to exactly one row in the roadmap document.
2. This exact draft-vs-actual numbering mismatch was already surfaced to the user in `PHASE-2-REPORT.md` §8, and the user's reply is best read as resolving it, not reopening it.
3. Verified the choice doesn't force placeholder work: row 3's own exit criterion ("A module can be enabled/disabled at runtime without touching core; events fire on lifecycle transitions") does not require persistence, and the row explicitly scopes to an internal, not-user-facing skeleton — consistent with what was actually built.

Logged as a decision in `DECISIONS.md`, not silently assumed.

## 1. Scope

Per the roadmap row, narrowly: `ModuleInterface`, `StandardInterface`, a Module Registry, a Standards Registry, the `ServiceProvider` pattern (introduced in Phase 2, used for real here for the first time), module lifecycle wiring (register → boot → enable/disable → remove), and one internal "probe" module/standard. No database tables, no migrations, no Settings Manager/Logger/Cache Service/Queue, no real user-facing Module, no Discovery/Validation/Generation/Scoring/Monitoring/Reporting engine logic, no REST, no admin UI.

## 2. What was implemented

### Contracts
| File | Purpose |
|---|---|
| `app/Contracts/ModuleInterface.php` | Full documented contract: identity (`id`/`name`/`version`/`description`/`author`) + lifecycle (`register`/`boot`/`init`/`shutdown`) + integration points (`assets`/`routes`/`settings`/`permissions`/`audit`) |
| `app/Contracts/StandardInterface.php` | Full documented contract: identity + spec metadata (`specification`/`supports`/`migrate`) + delegate methods (`discover`/`generate`/`validate`/`score`/`monitor`/`report`) |

### Core
| File | Purpose |
|---|---|
| `app/Exceptions/ModuleException.php` | Registry bookkeeping errors + Standard delegate-method "nothing to delegate to" errors |
| `app/Events/*.php` (7 files) | Lifecycle event DTOs (Module: Registered/Booted/Enabled/Disabled; Standard: Registered/Enabled/Disabled), fired via `do_action` |
| `app/Core/ModuleRegistry.php` | register/boot/bootAll/enable/disable/remove/isEnabled/isBooted/get/has/all — in-memory, event-firing |
| `app/Core/StandardsRegistry.php` | register/enable/disable/isEnabled/get/has/all — in-memory, event-firing |
| `app/Core/CoreServiceProvider.php` | Binds both registries as Container singletons — the first real use of Phase 2's `ServiceProvider` base class |

### Probe module (the roadmap row's "one minimal internal probe")
| File | Purpose |
|---|---|
| `app/Modules/Probe/ProbeModule.php` | Implements `ModuleInterface`; `assets()`/`routes()`/`settings()`/`permissions()`/`audit()` honestly return `[]` — it has none of these, and nothing exists yet to register them into |
| `app/Modules/Probe/ProbeStandard.php` | Implements `StandardInterface`; its six delegate methods throw `ModuleException` — the owning module has no Discovery provider/Generator/Validator/ScoreProvider/Monitor/Reporter registered, since those engines are later phases |
| `app/Modules/Probe/ProbeServiceProvider.php` | Registers the probe module+standard into their registries, boots the module |

### Modified
| File | Change |
|---|---|
| `app/Core/Bootstrap.php` | Now takes an ordered `ServiceProvider[]`; `run()` calls every `register()` then every `boot()` before marking the app booted — "Register Services"/"Load Core Components"/"Load Enabled Modules" now genuinely happen |
| `app/Core/Plugin.php` | Constructs `[CoreServiceProvider, ProbeServiceProvider]`, passes to `Bootstrap`; added `Plugin::boot()` (passthrough to `Kernel::boot()`) so tests can exercise the same path WordPress's real `plugins_loaded` firing would |
| `phpstan.neon` | Removed inherited `excludePaths: [app/Modules/*]` — see §3 |

### Tests
8 new/extended test files: `ModuleRegistryTest` (10), `StandardsRegistryTest` (5), `CoreServiceProviderTest` (1), `ProbeModuleTest` (3), `ProbeStandardTest` (3 methods, 8 executed cases via a data provider over the six delegate methods), `ProbeServiceProviderTest` (2), plus one new case each in `BootstrapTest` (provider register-before-boot ordering) and `PluginTest` (full end-to-end: `run()`+`boot()` really registers and boots the probe module through the real Container/Registry chain).

## 3. Checks performed — all run for real

- `composer validate` → valid.
- `composer test` → `OK (109 tests, 142 assertions)` — up from 78/89 at the end of Phase 2.
- `composer test:integration` → 0 tests (unchanged).
- PHPStan level 8 → `[OK] No errors` across 32 analysed files.
- PHPCS (hybrid ruleset) → 0 errors, 0 warnings across 55 files.
- `composer quality` → all green.

**One real gap caught, not just a style nit:** `phpstan.neon` still carried `excludePaths: [app/Modules/*]` from Phase 1 (added before any Module existed). With `app/Modules/Probe/*` now real code, PHPStan's "no errors" was silently skipping those three files — the analysed-file count (29, unchanged from before the new files existed) gave it away. Removed the exclusion; re-ran to a genuine 0 errors across 32 files. Flagged in `DECISIONS.md` as a reminder to check for stale path exclusions whenever a previously-empty directory gets real code.

## 4. Documentation updates

None to `docs/*` — implemented against the already-canonical docs. The two documented method lists for `ModuleInterface` (`docs/05-Modules.md`) and the base-class metadata methods (`docs/22-Plugin-SDK.md`) were merged into one interface rather than kept as two separate lists; this is additive reconciliation, not a doc edit, and is logged in `DECISIONS.md`.

## 5. Decisions requiring your attention

Full detail in `.project/DECISIONS.md`. Most likely to need a second look:

- **The Phase 3 scope-resolution call itself** (§0 above) — if "exactly according to the roadmap" was meant to pull in the draft's Phase 2 (DB infra) instead, this phase's content doesn't match that.
- **In-memory-only registry state.** `ModuleRegistry`/`StandardsRegistry` enable/disable state does not survive past the current request — there's no `oxy_modules`/`oxy_standards` table yet. This is fine per the exit criterion's own wording ("at runtime"), but is a real limitation until a DB-infra phase adds persistence.
- **`ProbeStandard`'s delegate methods throw rather than return data.** This is intentional (see `DECISIONS.md`), but means nothing can meaningfully call `discover()`/`generate()`/etc. on the probe standard yet — by design, since no owning engine exists.

## 6. Files created/modified this phase

16 new `app/` source files (2 Contracts, 1 Exception, 7 Events, 2 Core registries, 1 Core ServiceProvider, 3 Probe module files) + 6 new test files + 2 extended test files + 3 modified files (`Bootstrap.php`, `Plugin.php`, `phpstan.neon`) + `.project/PROGRESS.md`, `.project/DECISIONS.md`, `.project/FILE-MANIFEST.md` updated, this report new. Full manifest in `.project/FILE-MANIFEST.md`.

## 7. What's explicitly still missing (by design — later phases)

`Core/Scheduler.php`, any custom `oxy_*` database table or migration, Settings Manager, Logger service, Cache Service, Queue, any real user-facing Module (Robots/LLMS/Headers/Markdown/etc.), any Discovery/Validation/Generation/Scoring/Monitoring/Reporting engine, any REST endpoint, any admin UI, `package.json`/frontend tooling.

## 8. Recommendation for Phase 4

The user's instruction for this phase was explicit: "Do not implement any Phase 4 features." Per the draft roadmap, Phase 4 would be the Discovery Engine (resource/file/header/endpoint discovery pipeline → Discovery Map, `/discovery/*` REST) — but per the same numbering mismatch noted in §0, that's also contingent on whether "the roadmap" is being followed by row-label or by actual dependency order (Discovery's own draft dependency is "Phase 3," which now genuinely exists). I'll wait for explicit Phase 4 scope instruction rather than assume, same as after Phases 1 and 2.

---

**Stopping here. Waiting for approval before Phase 4.**
