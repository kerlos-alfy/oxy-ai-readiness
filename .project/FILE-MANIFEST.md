# File Manifest

Every file in the repository, grouped by origin. Updated at the end of each phase.

## Root instructions
| File | Purpose |
|---|---|
| `CLAUDE.md` | Claude Code project instructions (mandatory workflow, restrictions). |

## Documentation (`docs/`) — 30 spec files, updated in Phase 0.5 where noted
| File | Phase 0.5 edit? |
|---|---|
| `docs/01-Vision.md` | No |
| `docs/02-Architecture.md` | No |
| `docs/03-UI.md` | No |
| `docs/04-Folder-Structure.md` | Yes — canonical module structure (ADR-002) |
| `docs/05-Modules.md` | Yes — Module/Standard relationship (ADR-001) |
| `docs/06-Audit-Engine.md` | Yes — unified score table (ADR-005) |
| `docs/07-Robots-Spec.md` | No |
| `docs/08-LLMS-Spec.md` | No |
| `docs/09-Markdown-Spec.md` | No |
| `docs/10-Headers-Spec.md` | No |
| `docs/11-Content-Signals-Spec.md` | No |
| `docs/12-MCP-Spec.md` | No |
| `docs/13-Agent-Skills-Spec.md` | No |
| `docs/14-Discovery-Engine.md` | No |
| `docs/15-Scoring-Engine.md` | Yes — unified score table, removed AI READINESS LEVELS (ADR-005) |
| `docs/16-Validation-Engine.md` | No |
| `docs/17-Generation-Engine.md` | No |
| `docs/18-AutoFix-Engine.md` | No |
| `docs/19-Recommendation-Engine.md` | No |
| `docs/20-Monitoring-Engine.md` | No |
| `docs/21-Reporting-Engine.md` | No |
| `docs/22-Plugin-SDK.md` | Yes — canonical module structure, StandardInterface (ADR-001/002) |
| `docs/23-AI-Standards-Layer.md` | Yes — Module/Standard ownership table (ADR-001) |
| `docs/24-REST-API-Spec.md` | Yes — Monitoring/Reporting path fixes (ADR-003) |
| `docs/25-Database-Schema.md` | Yes — `setting_key`/`setting_value`, naming conventions (ADR-004) |
| `docs/26-Security-Spec.md` | No |
| `docs/27-Performance-Spec.md` | No |
| `docs/28-Testing-Strategy.md` | No |
| `docs/29-Developer-Guide.md` | Yes — Standard-in-Module example, asset path (ADR-001/002) |
| `docs/30-Claude-Code-Master.md` | No |

## Project control files (`.project/`)
| File | Purpose |
|---|---|
| `.project/00-Documentation-Index.md` | Phase 0 — index of all docs read |
| `.project/01-Requirement-Map.md` | Phase 0 — requirements by system area |
| `.project/02-Architecture-Dependency-Map.md` | Phase 0 — dependency graph |
| `.project/03-Conflicts.md` | Phase 0, updated Phase 0.5 — conflict tracking, now mostly resolved |
| `.project/04-Questions.md` | Phase 0, updated Phase 0.5 — open questions, now mostly resolved |
| `.project/05-Risks.md` | Phase 0 — risk register |
| `.project/06-Phase-Plan.md` | Phase 0 — draft phase plan (approved by user for Phases 0/0.5; Phase 1 scope was redefined directly by the user as "Repository Foundation" rather than this doc's original "Foundational scaffolding") |
| `.project/07-Progress-Log.md` | Phase 0/0.5 progress history (archival — see `PROGRESS.md` for Phase 1+) |
| `.project/08-Decision-Log.md` | Phase 0/0.5 decision history (archival — see `DECISIONS.md` for Phase 1+) |
| `.project/09-Canonical-Architecture.md` | Phase 0.5 — consolidated architecture reference |
| `.project/adr/ADR-001-modules-vs-standards.md` | Phase 0.5 |
| `.project/adr/ADR-002-folder-structure.md` | Phase 0.5 |
| `.project/adr/ADR-003-rest-api-naming.md` | Phase 0.5 |
| `.project/adr/ADR-004-database-naming.md` | Phase 0.5 |
| `.project/adr/ADR-005-scoring-grading.md` | Phase 0.5 |
| `.project/PROGRESS.md` | Phase 1+ — canonical progress record (this phase onward) |
| `.project/DECISIONS.md` | Phase 1+ — canonical decision record (this phase onward) |
| `.project/FILE-MANIFEST.md` | This file |
| `.project/PHASE-1-REPORT.md` | Phase 1 — full closeout report, updated with the real validation pass |
| `.project/TEST-STATUS.md` | Phase 1 validation pass — command-by-command execution log, environment fixes, PHPCS ruleset rationale |

## Build/tooling (repo root) — Phase 1
| File | Purpose |
|---|---|
| `composer.json` | PSR-4 autoload (`OxyAI\` → `app/`), dev dependencies (PHPUnit, Brain Monkey, Mockery, PHPStan + WP stubs, WPCS), `test`/`analyse`/`lint`/`quality` scripts |
| `phpcs.xml` | WordPress-Extra coding standard ruleset, PHP 8.1+ compatibility check |
| `phpstan.neon` | Level 8 static analysis, WordPress stub extension, scoped to `app/` |
| `phpunit.xml.dist` | Unit + Integration test suites, bootstrap, coverage config |

## Production source (`app/`) — Phase 1
| File | Purpose |
|---|---|
| `app/Contracts/RepositoryInterface.php` | Marker interface for every repository |
| `app/Repositories/OptionsRepository.php` | Wraps `wp_options` (get/update/delete_option) |
| `app/Repositories/TransientRepository.php` | Wraps WP transients + `remember()` cache-aside helper |
| `app/Repositories/UserRepository.php` | Wraps user lookup + capability checks |
| `app/Repositories/PostRepository.php` | Wraps post/page/CPT read access, bounded queries |
| `app/Repositories/FileRepository.php` | Wraps `WP_Filesystem_Base`, path-confined, atomic writes, checksums |

## Tests (`tests/`) — Phase 1
| File | Purpose |
|---|---|
| `tests/bootstrap.php` | PHPUnit bootstrap (composer autoload + WP core stubs) |
| `tests/stubs/wp-core-stubs.php` | Minimal local `WP_User`/`WP_Post`/`WP_Filesystem_Base`/`WP_Filesystem()` stand-ins |
| `tests/Unit/TestCase.php` | Base test case wiring Brain Monkey setUp/tearDown |
| `tests/Unit/Repositories/RepositoryContractTest.php` | Every repository implements `RepositoryInterface` and is `final` — 2 methods × 5 repositories (data provider) = 10 cases |
| `tests/Unit/Repositories/OptionsRepositoryTest.php` | 8 tests |
| `tests/Unit/Repositories/TransientRepositoryTest.php` | 8 tests |
| `tests/Unit/Repositories/UserRepositoryTest.php` | 7 tests |
| `tests/Unit/Repositories/PostRepositoryTest.php` | 8 tests |
| `tests/Unit/Repositories/FileRepositoryTest.php` | 13 tests |
| `tests/Integration/.gitkeep` | Empty placeholder — `phpunit.xml.dist` declares an `Integration` testsuite; no integration tests exist yet (out of scope for Phase 1), but PHPUnit fails to parse its config at all if a declared testsuite's directory doesn't exist |

**54 tests, 57 assertions total** — confirmed by actually running PHPUnit (see `TEST-STATUS.md`), not just counting `test_*` methods (46 declared methods; `RepositoryContractTest`'s 2 are multiplied ×5 by its data provider).

## Files touched during the 2026-07-24 validation pass (real `composer install`/PHPUnit/PHPStan/PHPCS execution)
| File | Change |
|---|---|
| `composer.json` | Added `phpcompatibility/phpcompatibility-wp` and `automattic/vipwpcs` to `require-dev`; `test` script now runs `phpunit --no-coverage` (no coverage driver installed in this environment); added `test:coverage` script for when one is |
| `composer.lock` | Regenerated by `composer install`/`update` |
| `vendor/`, `composer.lock` | Created by `composer install`/`update` (39 packages, then +2 `phpcompatibility/*`, then +2 `automattic/vipwpcs` + `sirbrillig/phpcs-variable-analysis` = 43 total dev dependencies). **No `.gitignore` exists yet** (repo is not git-initialized) — flagging that `vendor/` should not be committed once git is initialized, rather than assuming it's already excluded. |
| `phpcs.xml` | Rewritten from a bare `WordPress-Extra` ruleset to a hybrid: PSR-12 for formatting/naming, `WordPress.Security`/`WordPress.WP`/`WordPress.DB`/`WordPressVIPMinimum.Security` for WP-specific sniffs, plus 3 narrowly-scoped exclusions (test-method naming, WP-core stub naming, exception-message escaping false positive). User-approved decision — see `DECISIONS.md`. |
| `app/Repositories/FileRepository.php` | Real bug fix (found by PHPStan): `write()`'s directory creation now uses a proper recursive `ensureDirectoryExists()` helper instead of passing `true` as `WP_Filesystem_Base::mkdir()`'s undocumented-as-recursive third argument (that method has no recursive option — third/fourth args are `$chown`/`$chgrp`) |
| `tests/Unit/Repositories/FileRepositoryTest.php` | Updated `mkdir` mock expectation to match the corrected 2-argument call; PHPCS long-line fix |
| `tests/Unit/Repositories/PostRepositoryTest.php` | Added inline `phpcs:ignore` for a warning on a test that deliberately passes an out-of-range `posts_per_page` (5000) to exercise the clamp-to-100 behavior |
| `tests/Unit/Repositories/RepositoryContractTest.php` | Wrapped one line that exceeded 120 characters |
| `tests/Integration/.gitkeep` | New — see table above |
| `.project/TEST-STATUS.md` | New — full execution log |
| `.project/PROGRESS.md`, `.project/PHASE-1-REPORT.md`, `.project/FILE-MANIFEST.md` (this file) | Updated with validation-pass results |

**Totals as of end of Phase 1 validation:** 30 doc files (9 edited in Phase 0.5) + 1 root instructions file + 20 `.project/` control files (5 ADRs included) + 4 root tooling files + 6 `app/` source files + 10 `tests/` files (incl. `tests/Integration/.gitkeep`) = **71 hand-authored files**, plus `vendor/` (generated by Composer) and `composer.lock`. No `app/` files beyond `Contracts/` and `Repositories/` exist yet; no plugin bootstrap file exists yet; no `.gitignore` exists yet (repo not git-initialized).

## Root plugin files — Phase 2
| File | Purpose |
|---|---|
| `oxy-ai-readiness.php` | Plugin header, PHP-version guard, constructs `Plugin`, registers activation/deactivation hooks |
| `uninstall.php` | `WP_UNINSTALL_PLUGIN` guard only — nothing persistent exists yet to clean up |
| `readme.txt` | WP-standard plugin readme header + changelog |

## Production source (`app/`) — Phase 2
| File | Purpose |
|---|---|
| `app/Core/Container.php` | Minimal DI container: `bind()`/`singleton()`/`make()`/`has()`, zero-argument factories |
| `app/Core/Application.php` | Holds the `Container`, tracks boot state |
| `app/Core/Config.php` | Plugin-level metadata (version, plugin file/dir, text domain) |
| `app/Core/Hooks.php` | Registrar wrapping `add_action`/`add_filter` with bookkeeping |
| `app/Core/Bootstrap.php` | Idempotent boot sequence: marks `Application` booted, fires `oxy_ai_ready` |
| `app/Core/Kernel.php` | Registers the plugin on WordPress's `plugins_loaded`, delegates to `Bootstrap` |
| `app/Core/Plugin.php` | Top-level object; wires Container/Application/Kernel; owns activate()/deactivate() |
| `app/Providers/ServiceProvider.php` | Abstract base class (`register()`/`boot()`) for future Core/Module providers |

## Tests (`tests/`) — Phase 2
| File | Tests |
|---|---|
| `tests/Unit/Core/ContainerTest.php` | 5 |
| `tests/Unit/Core/ApplicationTest.php` | 3 |
| `tests/Unit/Core/ConfigTest.php` | 3 |
| `tests/Unit/Core/HooksTest.php` | 2 |
| `tests/Unit/Core/BootstrapTest.php` | 2 |
| `tests/Unit/Core/KernelTest.php` | 2 |
| `tests/Unit/Core/PluginTest.php` | 5 |
| `tests/Unit/Providers/ServiceProviderTest.php` | 2 |

**24 new test methods this phase — 78 tests, 89 assertions total repo-wide**, confirmed by actually running PHPUnit, PHPStan (level 8, 0 errors, now also scoped to the two root plugin files), and PHPCS (hybrid ruleset, 0 errors/0 warnings across all 33 files, including one narrowly file-scoped `PSR1.Files.SideEffects` exclusion for `oxy-ai-readiness.php` — see `DECISIONS.md`).

**Totals as of end of Phase 2:** adds 3 root plugin files + 8 `app/` source files + 8 test files to the Phase 1 count. No `Core/Scheduler.php`, `Core/ModuleRegistry.php`, `Core/StandardsRegistry.php`, database tables/migrations, Settings Manager, Logger, Cache Service, Queue, Modules, or REST routes exist yet — deferred to later phases.

## Production source (`app/`) — Phase 3
| File | Purpose |
|---|---|
| `app/Contracts/ModuleInterface.php` | Full documented Module contract (identity + register/boot/init/shutdown + assets/routes/settings/permissions/audit) |
| `app/Contracts/StandardInterface.php` | Full documented Standard contract; discover/generate/validate/score/monitor/report delegate to the owning Module's engines |
| `app/Exceptions/ModuleException.php` | Registry bookkeeping errors + "no engine registered yet" delegate-method errors |
| `app/Events/ModuleRegistered.php`, `ModuleBooted.php`, `ModuleEnabled.php`, `ModuleDisabled.php` | Module lifecycle event DTOs, fired via `do_action` |
| `app/Events/StandardRegistered.php`, `StandardEnabled.php`, `StandardDisabled.php` | Standard lifecycle event DTOs, fired via `do_action` |
| `app/Core/ModuleRegistry.php` | register/boot/enable/disable/remove/get/has/all, in-memory, fires lifecycle events |
| `app/Core/StandardsRegistry.php` | register/enable/disable/get/has/all, in-memory, fires lifecycle events |
| `app/Core/CoreServiceProvider.php` | Binds `ModuleRegistry`/`StandardsRegistry` as Container singletons — first real `ServiceProvider` consumer |
| `app/Modules/Probe/ProbeModule.php` | Internal, not-user-facing probe module proving the Module lifecycle end-to-end |
| `app/Modules/Probe/ProbeStandard.php` | Internal probe standard; delegate methods throw `ModuleException` (no owning engine exists yet) |
| `app/Modules/Probe/ProbeServiceProvider.php` | Registers/boots the probe module+standard into their registries |

## Modified this phase
| File | Change |
|---|---|
| `app/Core/Bootstrap.php` | Now takes a `ServiceProvider[]` list; `run()` calls every `register()` then every `boot()` before marking the app booted |
| `app/Core/Plugin.php` | Constructs `CoreServiceProvider`+`ProbeServiceProvider`, passes them to `Bootstrap`; added a `boot()` passthrough to `Kernel::boot()` for direct invocation (Brain Monkey's simulated `do_action` does not itself invoke registered callbacks) |
| `phpstan.neon` | Removed the `excludePaths: [app/Modules/*]` inherited from Phase 1 — it was silently skipping analysis of the new `app/Modules/Probe/*` files |

## Tests (`tests/`) — Phase 3
| File | Tests |
|---|---|
| `tests/Unit/Core/ModuleRegistryTest.php` | 10 |
| `tests/Unit/Core/StandardsRegistryTest.php` | 5 |
| `tests/Unit/Core/CoreServiceProviderTest.php` | 1 |
| `tests/Unit/Modules/Probe/ProbeModuleTest.php` | 3 |
| `tests/Unit/Modules/Probe/ProbeStandardTest.php` | 3 methods, 8 executed cases (one uses a 6-case data provider over the six delegate methods) |
| `tests/Unit/Modules/Probe/ProbeServiceProviderTest.php` | 2 |
| `tests/Unit/Core/BootstrapTest.php` | +1 (provider register-before-boot ordering) |
| `tests/Unit/Core/PluginTest.php` | +1 (end-to-end: `run()`+`boot()` really registers and boots the probe module through the real Container/Registry chain) |

**109 tests, 142 assertions total repo-wide** (up from 78/89 at the end of Phase 2), confirmed by actually running PHPUnit, PHPStan (level 8, 0 errors, now genuinely covering `app/Modules/*` too), and PHPCS (hybrid ruleset, 0 errors/0 warnings across 55 files).

**Totals as of end of Phase 3:** adds 2 Contracts + 1 Exception + 7 Event DTOs + 2 Core registries + 1 Core ServiceProvider + 3 Probe module files (16 new `app/` source files) + 8 test files (2 existing files extended) to the Phase 2 count. No `Core/Scheduler.php`, database tables/migrations, Settings Manager, Logger, Cache Service, Queue, any real feature Module (Robots/LLMS/etc.), or REST routes exist yet — still deferred to later phases.

## Production source (`app/`) — Phase 4
| File | Purpose |
|---|---|
| `app/DTO/DiscoveredResource.php` | Discovery Map entry value object (id/type/location/status/version/module/health/dependencies/source/lastChecked) + `toArray()` |
| `app/Contracts/DiscoveryInterface.php` | Per-module Discovery provider contract: `discover(): array<DiscoveredResource>` |
| `app/Services/DiscoveryService.php` | The Discovery Engine: registerProvider/scan/map/resources/reset, in-memory, lazy-scan-on-first-access, fires `oxy_ai_discovery_started`/`oxy_ai_resource_discovered`/`oxy_ai_discovery_finished` |
| `app/Core/RestServiceProvider.php` | First REST wiring: hooks `rest_api_init` (via the Container-bound `Hooks`), loads `routes/api.php` |
| `app/Http/Controllers/DiscoveryController.php` | GET-only controller: `index`/`map`/`resources`, gated by `current_user_can('manage_options')` |
| `routes/api.php` | Registers `GET /discovery`, `/discovery/map`, `/discovery/resources` under `oxy-ai/v1` |

## Modified this phase
| File | Change |
|---|---|
| `app/Core/CoreServiceProvider.php` | Also binds `DiscoveryService` as a Container singleton |
| `app/Modules/Probe/ProbeModule.php` | Now also implements `DiscoveryInterface`; `discover()` returns one fixture `DiscoveredResource` |
| `app/Modules/Probe/ProbeServiceProvider.php` | Registers the probe module with `DiscoveryService` too (single shared `ProbeModule` instance) |
| `app/Core/Plugin.php` | Binds `Hooks` as a Container singleton (same instance passed to `Kernel`, so `RestServiceProvider` and `Kernel` share one registrar); adds `RestServiceProvider` to the provider list |
| `phpstan.neon`, `phpcs.xml` | Added `routes/` to analysed/linted paths |
| `tests/stubs/wp-core-stubs.php` | Added minimal `WP_REST_Request`/`WP_REST_Response` stand-ins |

## Tests (`tests/`) — Phase 4
| File | Tests |
|---|---|
| `tests/Unit/Services/DiscoveryServiceTest.php` | 5 |
| `tests/Unit/Http/Controllers/DiscoveryControllerTest.php` | 4 |
| `tests/Unit/Core/RestServiceProviderTest.php` | 2 |
| `tests/Unit/Routes/ApiRoutesTest.php` | 1 |
| `tests/Unit/Core/CoreServiceProviderTest.php` | +1 (DiscoveryService singleton) |
| `tests/Unit/Modules/Probe/ProbeModuleTest.php` | +1 (discover() fixture) |
| `tests/Unit/Modules/Probe/ProbeServiceProviderTest.php` | extended existing case to also assert Discovery registration |

**123 tests, 165 assertions total repo-wide** (up from 109/142 at the end of Phase 3), confirmed by actually running PHPUnit, PHPStan (level 8, 0 errors, 38 files analysed — up from 32, confirming `routes/` is genuinely covered), and PHPCS (hybrid ruleset, 0 errors/0 warnings across 65 files).

**Totals as of end of Phase 4:** adds 1 DTO + 1 Contract + 1 Service + 1 Core ServiceProvider + 1 Http Controller + 1 routes file (6 new `app/`-or-root source files) + 4 new test files (3 existing files extended) to the Phase 3 count. No `Core/Scheduler.php`, database tables/migrations, Settings Manager, Logger, Cache Service, Queue, any real feature Module, any write/mutating REST route, or Validation/Generation/Scoring/Monitoring/Reporting engine exist yet — still deferred to later phases.

## Production source (`app/`) — Phase 5
| File | Purpose |
|---|---|
| `app/DTO/ValidationStatus.php` | Native backed enum: Pass/Warning/Fail/Info/Skipped/Unknown |
| `app/DTO/ValidationResult.php` | Outcome of one validator run (resourceId/validator/status/message/executionTimeMs) + `toArray()` |
| `app/Contracts/ValidatorInterface.php` | Per-module validator contract: `validate(DiscoveredResource): ValidationResult` |
| `app/Services/ValidationService.php` | The Validation Engine: registerValidator/validate/has/count, fires `oxy_ai_validation_started`/`completed`/`passed`/`failed`/`warning` |
| `app/Http/Controllers/ValidationController.php` | `index` (GET /validation) + `run` (POST /validation/run, validates `resource_id` input) |

## Modified this phase
| File | Change |
|---|---|
| `app/Core/CoreServiceProvider.php` | Also binds `ValidationService` as a Container singleton |
| `app/Modules/Probe/ProbeModule.php` | Now also implements `ValidatorInterface` — deterministic pass-iff-active rule |
| `app/Modules/Probe/ProbeServiceProvider.php` | Registers the probe module as a validator too; constructs `ProbeStandard` with the module |
| `app/Modules/Probe/ProbeStandard.php` | `discover()`/`validate()` now delegate to the owning module for real (Discovery/Validator exist as of Phases 4–5) instead of throwing; `generate()`/`score()`/`monitor()`/`report()` still throw |
| `routes/api.php` | Adds `GET /validation`, `POST /validation/run` |
| `composer.json` | `analyse` script now bakes in `--memory-limit=512M` — see Decisions |

## Tests (`tests/`) — Phase 5
| File | Tests |
|---|---|
| `tests/Unit/Services/ValidationServiceTest.php` | 4 |
| `tests/Unit/Http/Controllers/ValidationControllerTest.php` | 5 |
| `tests/Unit/Modules/Probe/ProbeModuleTest.php` | +2 (validate pass/fail) |
| `tests/Unit/Modules/Probe/ProbeStandardTest.php` | rewritten: 5 methods (discover/validate delegation + 4-case data provider for the still-throwing methods) |
| `tests/Unit/Modules/Probe/ProbeServiceProviderTest.php` | extended to assert validator registration |
| `tests/Unit/Core/CoreServiceProviderTest.php` | +1 (ValidationService singleton) |
| `tests/Unit/Routes/ApiRoutesTest.php` | rewritten to cover all 5 routes (3 Discovery + 2 Validation) |

**135 tests, 190 assertions total repo-wide** (up from 123/165 at the end of Phase 4), confirmed by actually running PHPUnit, PHPStan (level 8, 0 errors, 43 files analysed — up from 38), and PHPCS (hybrid ruleset, 0 errors/0 warnings across 72 files).

**Totals as of end of Phase 5:** adds 2 DTOs + 1 Contract + 1 Service + 1 Http Controller (5 new `app/` source files) + 2 new test files (5 existing files extended) to the Phase 4 count. No `Core/Scheduler.php`, database tables/migrations, Settings Manager, Logger, Cache Service, Queue, any real feature Module, or Generation/Scoring/Monitoring/Reporting engine exist yet — still deferred to later phases.

## Production source (`app/`) — Phase 6
| File | Purpose |
|---|---|
| `app/DTO/GenerationResult.php` | Publish outcome: generatorId/path/version/checksum/publishedAt + `toArray()` |
| `app/Contracts/GeneratorInterface.php` | Minimal per-generator contract: id/resourceId/supports/generate (engine owns publish/rollback/cache/version — see Decisions) |
| `app/Exceptions/GenerationException.php` | Publish-with-failed-validation, publish-of-undiscovered-resource, write failure, rollback-with-nothing-to-restore |
| `app/Services/GenerationService.php` | The Generation Engine: registerGenerator/generate/preview/cache/publish/rollback/version/currentContent, two-slot (current + previous) versioning via `FileRepository`, fires `oxy_ai_generation_*` events |
| `app/Http/Controllers/GenerationController.php` | `index`/`preview`/`publish`/`rollback` — validates `generator_id`, translates `GenerationException` into 409 |

## Modified this phase
| File | Change |
|---|---|
| `app/Core/CoreServiceProvider.php` | Also binds `GenerationService` (constructs a `FileRepository` scoped to `storage/generated/` under the plugin's own root, via `Config`) |
| `app/Core/Plugin.php` | `activate()` now also calls `wp_mkdir_p()` to ensure `storage/generated/` exists — `FileRepository` never creates its own base directory, only subdirectories beneath it |
| `app/Modules/Probe/ProbeModule.php` | Now also implements `GeneratorInterface` — fixed, deterministic content tied to its own discovered resource |
| `app/Modules/Probe/ProbeServiceProvider.php` | Registers the probe module as a generator too |
| `app/Modules/Probe/ProbeStandard.php` | `generate()` now delegates to the owning module for real; only `score()`/`monitor()`/`report()` still throw |
| `routes/api.php` | Adds `GET /generation`, `GET /generation/preview`, `POST /generation/publish`, `POST /generation/rollback` |

## Tests (`tests/`) — Phase 6
| File | Tests |
|---|---|
| `tests/Unit/Support/InMemoryFilesystem.php` | Test double (not a test file) — in-memory `WP_Filesystem_Base` for exercising `FileRepository` read/write/move sequences without Mockery call-by-call brittleness |
| `tests/Unit/Services/GenerationServiceTest.php` | 7 |
| `tests/Unit/Http/Controllers/GenerationControllerTest.php` | 8 |
| `tests/Unit/Modules/Probe/ProbeModuleTest.php` | +3 (resourceId/supports/generate) |
| `tests/Unit/Modules/Probe/ProbeStandardTest.php` | `generate()` moved out of the still-throwing data provider into its own delegation assertion |
| `tests/Unit/Modules/Probe/ProbeServiceProviderTest.php` | extended to assert generator registration |
| `tests/Unit/Core/CoreServiceProviderTest.php` | +1 (GenerationService singleton, using a bound `Config` for its storage path) |
| `tests/Unit/Core/PluginTest.php` | activate() tests updated for the new `wp_mkdir_p()` call |
| `tests/Unit/Routes/ApiRoutesTest.php` | rewritten to cover all 9 routes (6 GET + 3 POST) |

**154 tests, 228 assertions total repo-wide** (up from 135/190 at the end of Phase 5), confirmed by actually running PHPUnit, PHPStan (level 8, 0 errors, 48 files analysed — up from 43), and PHPCS (hybrid ruleset, 0 errors/0 warnings across 80 files).

**Totals as of end of Phase 6:** adds 1 DTO + 1 Contract + 1 Exception + 1 Service + 1 Http Controller (5 new `app/` source files) + 1 test-support file + 2 new test files (5 existing files extended) to the Phase 5 count. No `Core/Scheduler.php`, database tables/migrations, Settings Manager, Logger, Cache Service, Queue, any real feature Module, or Scoring/Monitoring/Reporting engine exist yet — still deferred to later phases.

## Production source (`app/`) — Phase 7
| File | Purpose |
|---|---|
| `app/DTO/Grade.php` | Canonical Score→Grade→Label enum (ADR-005), `fromScore()`/`label()` |
| `app/DTO/Trend.php` | Improving/Stable/Declining/Unknown enum |
| `app/DTO/ConfidenceLevel.php` | VeryHigh/High/Medium/Low enum, `fromRatio()` |
| `app/DTO/ScoreResult.php` | score/grade/confidence/trend/calculatedAt + `toArray()` |
| `app/Services/ScoringService.php` | The Scoring Engine: `calculate(array ValidationResult): ScoreResult` — status-weighted (Pass=1.0/Warning=0.5/Fail=0.0), confidence from applicable-vs-total ratio, in-memory trend, fires `oxy_ai_score_calculated`/`grade_changed`/`trend_updated`/`confidence_updated` |
| `app/Http/Controllers/ScoreController.php` | `index` (GET /score) — chains Discovery map → Validation results → Scoring |

## Modified this phase
| File | Change |
|---|---|
| `app/Core/CoreServiceProvider.php` | Also binds `ScoringService` as a Container singleton (no per-module registration — it's a stateless calculator, not a registry) |
| `routes/api.php` | Adds `GET /score` |

## Tests (`tests/`) — Phase 7
| File | Tests |
|---|---|
| `tests/Unit/DTO/GradeTest.php` | 1 method, 20 data-provider cases covering every grade boundary exactly (the Phase 7 exit criterion's own words: "grade boundary unit tests pass") |
| `tests/Unit/Services/ScoringServiceTest.php` | 7 |
| `tests/Unit/Http/Controllers/ScoreControllerTest.php` | 3 |
| `tests/Unit/Core/CoreServiceProviderTest.php` | +1 (ScoringService singleton) |
| `tests/Unit/Routes/ApiRoutesTest.php` | extended to cover the 10th route |

**185 tests, 292 assertions total repo-wide** (up from 154/228 at the end of Phase 6), confirmed by actually running PHPUnit, PHPStan (level 8, 0 errors, 54 files analysed — up from 48), and PHPCS (hybrid ruleset, 0 errors/0 warnings across 89 files, including one narrow inline suppression for a PHPCompatibility false positive on `$this` inside an enum method — see Decisions).

**Totals as of end of Phase 7:** adds 4 DTOs + 1 Service + 1 Http Controller (6 new `app/` source files) + 3 new test files (2 existing files extended) to the Phase 6 count. No `Core/Scheduler.php`, database tables/migrations, Settings Manager, Logger, Cache Service, Queue, any real feature Module, or Monitoring/Reporting engine exist yet — still deferred to later phases.

## Production source (`app/`) — Phase 8
| File | Purpose |
|---|---|
| `app/Modules/Robots/RobotsRule.php` | Per-User-agent rule value object (userAgent/disallow/allow/crawlDelay) |
| `app/Modules/Robots/RobotsModule.php` | The first real, user-facing module: `ModuleInterface`+`DiscoveryInterface`+`ValidatorInterface`+`GeneratorInterface`, fixed default ruleset (WP-standard `/wp-admin/` disallow + "Allow AI" template for GPTBot/ChatGPT-User/Google-Extended/ClaudeBot/PerplexityBot) |
| `app/Modules/Robots/RobotsStandard.php` | Owns the `robots.txt` Standard per ADR-001; delegates discover/validate/generate; score/monitor/report still throw |
| `app/Modules/Robots/RobotsServiceProvider.php` | Registers the module into ModuleRegistry/DiscoveryService/ValidationService/GenerationService/StandardsRegistry |
| `app/Http/Controllers/RobotsController.php` | `/robots/*` facade over the shared engines: `index`/`preview`/`save`/`validate`/`reset` |

## Modified this phase
| File | Change |
|---|---|
| `app/Core/Plugin.php` | Adds `RobotsServiceProvider` to the provider list |
| `routes/api.php` | Adds `GET /robots`, `GET /robots/preview`, `POST /robots/save`, `POST /robots/validate`, `POST /robots/reset` |

## Tests (`tests/`) — Phase 8
| File | Tests |
|---|---|
| `tests/Unit/Modules/Robots/RobotsModuleSnapshotTest.php` | 1 — the exit criterion's own "snapshot test on generated robots.txt," freezing the exact expected output byte-for-byte |
| `tests/Unit/Modules/Robots/RobotsModuleTest.php` | 6 |
| `tests/Unit/Modules/Robots/RobotsStandardTest.php` | 3 methods, 5 executed cases (data provider over the three still-throwing methods) |
| `tests/Unit/Modules/Robots/RobotsServiceProviderTest.php` | 2 |
| `tests/Unit/Http/Controllers/RobotsControllerTest.php` | 7 |
| `tests/Unit/EndToEnd/RobotsScoringEndToEndTest.php` | 1 — proves the exit criterion's "audit rule shows in Scoring output" using the real `Plugin` wiring, not mocks |
| `tests/Unit/Routes/ApiRoutesTest.php` | rewritten to cover all 15 routes (9 GET + 6 POST) |

**207 tests, 345 assertions total repo-wide** (up from 185/292 at the end of Phase 7), confirmed by actually running PHPUnit, PHPStan (level 8, 0 errors, 59 files analysed — up from 54), and PHPCS (hybrid ruleset, 0 errors/0 warnings across 100 files).

**Totals as of end of Phase 8:** adds 1 DTO + 4 Module files + 1 Http Controller (6 new `app/` source files) + 6 new test files (1 existing file extended) to the Phase 7 count. No `Core/Scheduler.php`, database tables/migrations, Settings Manager, Logger, Cache Service, Queue, any other real feature Module, or Monitoring/Reporting engine exist yet — still deferred to later phases.

## Production source (`app/`) — Phase 9
| File | Purpose |
|---|---|
| `app/DTO/ScanType.php` | Quick/Full/Deep/Developer enum |
| `app/DTO/AuditReport.php` | scanType/results/summary/score/durationMs/startedAt + `toArray()` |
| `app/Services/AuditService.php` | The Audit Engine: `scan(ScanType): AuditReport` — ties Discovery+Validation+Scoring together; Quick reuses the cached Discovery Map, Full/Deep/Developer force a fresh discovery pass first |
| `app/Http/Controllers/AuditController.php` | `GET /audit` (last report), `POST /audit/start` (type param) |

## Modified this phase
| File | Change |
|---|---|
| `app/Core/CoreServiceProvider.php` | Also binds `AuditService` as a Container singleton |
| `routes/api.php` | Adds `GET /audit`, `POST /audit/start` |

## Tests (`tests/`) — Phase 9
| File | Tests |
|---|---|
| `tests/Unit/Services/AuditServiceTest.php` | 3 methods, 6 executed cases (one uses a 4-case data provider over every scan type's performance target) |
| `tests/Unit/Http/Controllers/AuditControllerTest.php` | 6 |
| `tests/Unit/Core/CoreServiceProviderTest.php` | +1 (AuditService singleton) |
| `tests/Unit/Routes/ApiRoutesTest.php` | extended to cover the 2 new routes |

**220 tests, 368 assertions total repo-wide** (up from 207/345 at the end of Phase 8), confirmed by actually running PHPUnit, PHPStan (level 8, 0 errors, 63 files analysed — up from 59), and PHPCS (hybrid ruleset, 0 errors/0 warnings across 106 files).

**Totals as of end of Phase 9:** adds 2 DTOs + 1 Service + 1 Http Controller (4 new `app/` source files) + 2 new test files (2 existing files extended) to the Phase 8 count. No `Core/Scheduler.php`, database tables/migrations, Settings Manager, Logger, Cache Service, Queue, Recommendation/AutoFix/Monitoring/Reporting engines, or any other real feature Module exist yet — still deferred to later phases.

## Production source (`app/`) — Phase 10
| File | Purpose |
|---|---|
| `app/DTO/FixTier.php` | Safe/Confirmation/Developer enum |
| `app/DTO/FixResult.php` | generatorId/success/version/message/pending + `toArray()` |
| `app/DTO/Recommendation.php` | id/title/description/category/priority/autoFixAvailable + `toArray()` |
| `app/Services/RecommendationService.php` | Turns FAIL/WARNING `ValidationResult`s into `Recommendation`s; `autoFixAvailable` genuinely checked against `GenerationService` |
| `app/Services/AutoFixService.php` | The Auto Fix Engine: `fix()` reuses `GenerationService`'s existing backup/execute/validate pipeline, adds an explicit post-fix verify step, rolls back on verification failure; tracks `lastResult()` |
| `app/Http/Controllers/RecommendationController.php` | `GET /recommendations`, `POST /recommendations/generate` |
| `app/Http/Controllers/AutoFixController.php` | `GET /autofix`, `POST /autofix/run`, `POST /autofix/rollback` |

## Modified this phase
| File | Change |
|---|---|
| `app/Services/GenerationService.php` | Adds `resourceIdFor()` — lets `AutoFixService` re-check a resource after publishing without its own separate lookup |
| `app/Core/CoreServiceProvider.php` | Also binds `RecommendationService`/`AutoFixService` as Container singletons |
| `routes/api.php` | Adds `GET /recommendations`, `POST /recommendations/generate`, `GET /autofix`, `POST /autofix/run`, `POST /autofix/rollback` |

## Tests (`tests/`) — Phase 10
| File | Tests |
|---|---|
| `tests/Unit/Services/RecommendationServiceTest.php` | 3 |
| `tests/Unit/Services/AutoFixServiceTest.php` | 10 — the docs/28 AutoFix Safety Tests subset: backup/execute/verify/rollback, rollback after Validation Failure and Filesystem Failure specifically, confirmation-tier gating |
| `tests/Unit/Http/Controllers/RecommendationControllerTest.php` | 3 |
| `tests/Unit/Http/Controllers/AutoFixControllerTest.php` | 10 |
| `tests/Unit/Services/GenerationServiceTest.php` | +1 (`resourceIdFor()`) |
| `tests/Unit/Core/CoreServiceProviderTest.php` | +2 (RecommendationService, AutoFixService singletons) |
| `tests/Unit/Routes/ApiRoutesTest.php` | extended to cover the 5 new routes |

**249 tests, 424 assertions total repo-wide** (up from 220/368 at the end of Phase 9), confirmed by actually running PHPUnit, PHPStan (level 8, 0 errors, 70 files analysed — up from 63), and PHPCS (hybrid ruleset, 0 errors/0 warnings across 117 files).

**Totals as of end of Phase 10:** adds 3 DTOs + 2 Services + 2 Http Controllers (7 new `app/` source files) + 4 new test files (3 existing files extended) to the Phase 9 count. No `Core/Scheduler.php`, database tables/migrations, Settings Manager, Logger, Cache Service, Queue, Monitoring/Reporting engines, or any other real feature Module exist yet — still deferred to later phases.

## Production source (`app/`) — Phase 11
Four modules, each repeating the exact `Modules/Robots` (Phase 8) shape: `{X}Module` (Module+Discovery+Validator+Generator), `{X}Standard` (where ADR-001 gives the module a Standard), `{X}ServiceProvider`, `{X}Controller`.

| Module | Files | Owns a Standard? | Default content |
|---|---|---|---|
| LLMS | `app/Modules/Llms/{LlmsModule,LlmsStandard,LlmsServiceProvider}.php`, `app/Http/Controllers/LlmsController.php` | Yes — `llms-txt` | Title + description blockquote (docs/01-Vision.md's own Plugin Name/Tagline) |
| Headers | `app/Modules/Headers/{HeadersModule,HeadersServiceProvider}.php`, `app/Http/Controllers/HeadersController.php` | **No** — ADR-001 explicitly lists Headers as owning no Standard | `Content-Signal`/`X-Content-Type-Options`/`Referrer-Policy` declaration, one `Name: value` per line |
| Markdown | `app/Modules/Markdown/{MarkdownModule,MarkdownStandard,MarkdownServiceProvider}.php`, `app/Http/Controllers/MarkdownController.php` | Yes — `markdown-negotiation` | Negotiation capability declaration (Content-Type/Accept types, not per-page converted content) |
| Content Signals | `app/Modules/ContentSignals/{ContentSignalsModule,ContentSignalsStandard,ContentSignalsServiceProvider}.php`, `app/Http/Controllers/ContentSignalsController.php` | Yes — `content-signals` | Site-wide AI usage signals (`ai-training`/`ai-citation`/`ai-summary`) |

## Modified this phase
| File | Change |
|---|---|
| `app/Core/Plugin.php` | Adds all 4 new `ServiceProvider`s to the provider list |
| `routes/api.php` | Adds `/llms/*`, `/headers/*`, `/markdown/*`, `/content-signals/*` (5 routes each, mirroring `/robots/*`) |

## Tests (`tests/`) — Phase 11
Each module has the same test shape as Robots: `{X}ModuleSnapshotTest` (1 — frozen exact output), `{X}ModuleTest` (6), `{X}StandardTest` (3 methods/5 cases, skipped for Headers), `{X}ServiceProviderTest` (2, or 3 for Headers which also asserts no Standard is registered), `{X}ControllerTest` (7). `tests/Unit/Routes/ApiRoutesTest.php` rewritten to generate its expected-routes list from a module-slug loop rather than hand-listing 20 more routes.

**329 tests, 612 assertions total repo-wide** (up from 249/424 at the end of Phase 10), confirmed by actually running PHPUnit, PHPStan (level 8, 0 errors, 85 files analysed — up from 70), and PHPCS (hybrid ruleset, 0 errors/0 warnings across 151 files) — all clean on the first run.

**Totals as of end of Phase 11:** adds 4 Modules (14 Module/Standard/ServiceProvider files) + 4 Http Controllers (18 new `app/` source files) + 19 new test files (1 existing file rewritten) to the Phase 10 count. No `Core/Scheduler.php`, database tables/migrations, Settings Manager, Logger, Cache Service, Queue, Monitoring/Reporting engines, MCP/Agent Skills/API Catalog/OAuth modules, or an admin UI exist yet — still deferred to later phases.

## Production source (`app/`) — Phase 12
| File | Purpose |
|---|---|
| `app/Admin/AdminServiceProvider.php` | Registers the single top-level wp-admin menu page, mounts the SPA's `#oxy-ai-readiness-root` node, enqueues the Vite-built bundle (reading `dist/.vite/manifest.json` via an injectable `FileRepository`, no hardcoded hashed filenames), marks its script tag `type="module"`, localizes `window.oxyAiReadiness` (`restUrl`/`nonce`/`version`), and falls back to an `admin_notices` error (not a fatal) if `dist/` hasn't been built yet |

## Modified this phase
| File | Change |
|---|---|
| `app/Core/Plugin.php` | Adds `AdminServiceProvider` to the provider list |

## Frontend source (`assets/react/`) — Phase 12
Already existed, built and left uncommitted by a prior session; verified real (not placeholder) and landed this phase alongside its config/tooling. One centralized React/TS SPA per docs/03-UI.md and docs/04-Folder-Structure.md's "no per-module Views/Assets, single centralized React SPA" note — not per-module server-rendered views.

| File | Purpose |
|---|---|
| `assets/react/main.tsx` | Mounts `<App />` into `#oxy-ai-readiness-root` |
| `assets/react/App.tsx` | Owns in-page navigation between Dashboard/Audit/module screens (no server-side routing) |
| `assets/react/Layouts/{Sidebar,Header}.tsx` (+ `Sidebar.test.tsx`) | Nav (only entries with a real REST-backed screen — no API Catalog/MCP/Agent Skills/Commerce/Logs/Settings/About yet) + top header |
| `assets/react/Dashboard/DashboardPage.tsx` (+ `.test.tsx`) | Answers docs/03's 3 Dashboard Goal questions from live `/score`, `/audit`, `/recommendations` — no mock data; `.test.tsx` asserts real payload handling and a jest-axe a11y pass |
| `assets/react/Audit/AuditPage.tsx` | `/audit`, `POST /audit/start` — checklist + Run Audit |
| `assets/react/Components/ModulePage.tsx` | One screen definition reused by all 5 Phase 8/11 modules (`/{slug}`, `/{slug}/preview`, `/{slug}/save`, `/{slug}/validate`, `/{slug}/reset`) |
| `assets/react/{Robots,Llms,Markdown,Headers,ContentSignals}/*Page.tsx` | Thin `ModulePage` instantiations, one per module |
| `assets/react/Components/{Badge,Button,Card,ScoreCircle}.tsx` (+ 2 `.test.tsx`) | Shared design-system primitives per docs/03-UI.md's color/radius/shadow tokens |
| `assets/react/Hooks/useApi.ts`, `assets/react/Utils/api.ts` | `useApiGet` + `apiGet`/`apiPost` REST client reading `window.oxyAiReadiness` |
| `assets/react/Types/api.ts` | TS interfaces mirroring PHP DTOs' `toArray()` shapes |
| `assets/react/test/{mockFetch.ts,setupTests.ts,styleMock.cjs}` | Test fixtures/setup (jest-axe matcher, `window.oxyAiReadiness` stub, route-mocked fetch) |
| `assets/react/index.css` | Tailwind entry |

## Tooling/config — Phase 12
| File | Purpose |
|---|---|
| `package.json`, `package-lock.json` | npm scripts (`lint`/`typecheck`/`test`/`build`/`quality`), pinned dependency versions |
| `vite.config.ts` | Builds `assets/react/main.tsx` → `dist/`, `build.manifest: true` |
| `tsconfig.json`, `tsconfig.jest.json` | Strict TS config for the app + a CommonJS variant for ts-jest |
| `jest.config.cjs` | jsdom test environment, ts-jest transform, CSS module mock |
| `eslint.config.js` | Flat ESLint config: TS/React/react-hooks/jsx-a11y, `no-undef` off for `.ts`/`.tsx` (superseded by `tsc --noEmit`) |
| `tailwind.config.js`, `postcss.config.js` | docs/03-UI.md's color palette/radius/shadow tokens as Tailwind theme extensions |
| `.prettierrc.json` | Formatting only, not enforced by `npm run quality` |

## Tests (`tests/`) — Phase 12
| File | Tests |
|---|---|
| `tests/Unit/Admin/AdminServiceProviderTest.php` | 9 — hook registration, `admin_menu`'s exact `add_menu_page()` args, enqueue page-suffix guard, missing-build notice path, full enqueue+localize path, `renderRoot`/`renderMissingBuildNotice` output, `script_loader_tag` handle-scoping |

PHP: **338 tests, 622 assertions total repo-wide** (up from 329/612 at the end of Phase 11), confirmed by actually running PHPUnit, PHPStan (level 8, 0 errors, 86 files analysed — up from 85), and PHPCS (hybrid ruleset, 0 errors/0 warnings across 154 files).

Frontend (run for the first time this phase): `npm run lint` (ESLint, 0 problems), `npm run typecheck` (`tsc --noEmit`, 0 errors), `npm run test` (Jest, 4 suites/6 tests, including a jest-axe a11y assertion), `npm run build` (`tsc --noEmit && vite build`, confirmed producing `dist/.vite/manifest.json` in the exact shape `AdminServiceProvider` reads).

**Totals as of end of Phase 12:** adds 1 `app/` source file (`AdminServiceProvider`) + 1 new PHP test file (9 tests) to the Phase 11 count, plus the entire frontend (SPA source + tooling/config) landed and verified working for the first time. No `Core/Scheduler.php`, database tables/migrations, Settings Manager, Logger, Cache Service, Queue, Monitoring/Reporting engines, or MCP/Agent Skills/API Catalog/OAuth modules exist yet — still deferred to later phases. No nav/screen exists yet for any module without a REST backend (API Catalog, MCP, Agent Skills, Commerce, Logs, Settings, About).

## Production source (`app/`) — Phase 13
| File | Purpose |
|---|---|
| `app/DTO/ChangeType.php` | Created/Modified/Deleted enum (3 of docs/20's 9 documented cases — the rest need live HTTP/SSL/lifecycle infra this project doesn't have) |
| `app/DTO/NotificationPriority.php` | Critical/High/Medium/Low/Informational enum (all 5 documented cases; only Critical/Medium/Informational currently reachable — no severity axis exists on `ValidationResult`) |
| `app/DTO/MonitoringEvent.php` | resourceId/changeType/results/priority/message/detectedAt + `toArray()` |
| `app/DTO/ExportFormat.php` | Json/Markdown enum (2 of docs/21's 8 documented formats — both genuinely implemented) |
| `app/DTO/Report.php` | id/generatedAt/auditReport/recommendations/monitoringEvents + `toArray()` — docs/21's own "Technical Report" shape |
| `app/Services/MonitoringService.php` | The Monitoring Engine: `start()`/`stop()`/`reset()` arm state; `scan()` diffs a Discovery-metadata-plus-generated-content fingerprint per resource against the last-known baseline, revalidates anything changed via `ValidationService`, fires `oxy_ai_resource_changed`/`oxy_ai_notification_sent` |
| `app/Services/ReportService.php` | The Reporting Engine: `generate()` aggregates a fresh `AuditService` scan + derived `Recommendation`s + current `MonitoringService` events into a `Report`; `export()` renders it as JSON or Markdown |
| `app/Http/Controllers/MonitoringController.php` | `GET /monitoring`, `/monitoring/status`, `/monitoring/events`; `POST /monitoring/start`, `/stop`, `/reset`, `/scan` |
| `app/Http/Controllers/ReportController.php` | `GET /reports`; `POST /reports/generate`, `/reports/export` |

## Modified this phase
| File | Change |
|---|---|
| `app/Core/CoreServiceProvider.php` | Also binds `MonitoringService`/`ReportService` as Container singletons |
| `routes/api.php` | Adds `GET /monitoring`, `/monitoring/status`, `/monitoring/events`, `GET /reports`; `POST /monitoring/start`, `/stop`, `/reset`, `/scan`, `POST /reports/generate`, `/reports/export` |

## Tests (`tests/`) — Phase 13
| File | Tests |
|---|---|
| `tests/Unit/Services/MonitoringServiceTest.php` | 8 — no-baseline no-op, no-change, modified (+ revalidation/notification), created, deleted (+ critical priority), content-only change via a registered Generator, stop, reset |
| `tests/Unit/Http/Controllers/MonitoringControllerTest.php` | 8 |
| `tests/Unit/Services/ReportServiceTest.php` | 5 — aggregation, default scan type, null before first generate, JSON round-trip, Markdown section coverage |
| `tests/Unit/Http/Controllers/ReportControllerTest.php` | 8 |
| `tests/Unit/Core/CoreServiceProviderTest.php` | +2 (MonitoringService, ReportService singletons) |
| `tests/Unit/Routes/ApiRoutesTest.php` | extended to cover the 11 new routes |

**369 tests, 701 assertions total repo-wide** (up from 338/622 at the end of Phase 12), confirmed by actually running PHPUnit, PHPStan (level 8, 0 errors, 95 files analysed — up from 86), and PHPCS (hybrid ruleset, 0 errors/0 warnings across 166 files) — all clean on the first run.

**Totals as of end of Phase 13:** adds 5 DTOs + 2 Services + 2 Http Controllers (9 new `app/` source files) + 4 new test files (2 existing files extended) to the Phase 12 count. No `Core/Scheduler.php`, database tables/migrations, Settings Manager, Logger, Cache Service, Queue, or MCP/Agent Skills/API Catalog/OAuth Discovery modules exist yet — still deferred to later phases.

## Production source (`app/`) — Phase 14
| Module | Files | Owns a Standard? | Default content |
|---|---|---|---|
| MCP | `app/Modules/Mcp/{McpModule,McpStandard,McpServiceProvider}.php`, `app/Http/Controllers/McpController.php` | Yes — `mcp` (`https://modelcontextprotocol.io`) | Server Card identity (name/description/organization/version); capabilities/resources/tools/prompts honestly empty — no live MCP transport exists |
| Agent Skills | `app/Modules/AgentSkills/{AgentSkillsModule,AgentSkillsStandard,AgentSkillsServiceProvider}.php`, `app/Http/Controllers/AgentSkillsController.php` | Yes — `agent-skills` (internal identifier) | 3 real skills mapping to this plugin's own working REST actions (Score/Audit/Recommendations) |
| API Catalog | `app/Modules/ApiCatalog/{ApiCatalogModule,ApiCatalogStandard,ApiCatalogServiceProvider}.php`, `app/Http/Controllers/ApiCatalogController.php` | Yes — `api-catalog` (internal identifier) | Hand-maintained, accurate 83-route inventory of every real `oxy-ai/v1` REST route |
| OAuth Discovery | `app/Modules/OAuthDiscovery/{OAuthDiscoveryModule,OAuthDiscoveryServiceProvider,OpenIdConfigurationGenerator,OAuthAuthorizationServerGenerator,OAuthProtectedResourceGenerator,OpenIdConfigurationStandard,OAuthAuthorizationServerStandard,OAuthProtectedResourceStandard}.php`, `app/Http/Controllers/{OAuthDiscoveryController,OAuthDiscoveryFileController}.php` | **Three** — `openid-configuration`, `oauth-authorization-server` (RFC 8414), `oauth-protected-resource` (RFC 9728) | `oauth-protected-resource` is fully real (this plugin's own REST namespace); the other two publish only a real issuer + an honest "not configured" note, since no OAuth/OIDC server exists |

## Modified this phase
| File | Change |
|---|---|
| `app/Core/Plugin.php` | Adds all 4 new `ServiceProvider`s to the provider list |
| `routes/api.php` | Adds `/mcp/*`, `/agent-skills/*`, `/api-catalog/*` (5 routes each) + `/oauth-discovery` + `/oauth-discovery/{document}/*` ×3 (16 routes total) |
| `tests/Unit/EndToEnd/RobotsScoringEndToEndTest.php` | Adds `home_url()`/`rest_url()` stubs — the first real WordPress function calls reachable from this full-system validate-everything-against-everything test path |
| `tests/Unit/Routes/ApiRoutesTest.php` | Extended to cover the 44 new routes; adds a regression assertion cross-checking `ApiCatalogModule`'s route count against the actual registered count |

## Tests (`tests/`) — Phase 14
Each of MCP/Agent Skills/API Catalog has the same test shape as Phase 11's modules: `{X}ModuleTest` (6; API Catalog has no separate SnapshotTest — see its own file's docblock for why), `{X}StandardTest` (3 methods/5 cases), `{X}ServiceProviderTest` (2), `{X}ControllerTest` (7). MCP/Agent Skills also have a `{X}ModuleSnapshotTest` (1). OAuth Discovery has its own shape: `OAuthDiscoveryGeneratorsTest` (6, covering all 3 Generators), `OAuthDiscoveryModuleTest` (7), one `*StandardTest` per Standard (3 files, ~6 cases each), `OAuthDiscoveryServiceProviderTest` (2), `OAuthDiscoveryControllerTest` (2) + `OAuthDiscoveryFileControllerTest` (7).

**475 tests, 958 assertions total repo-wide** (up from 369/701 at the end of Phase 13), confirmed by actually running PHPUnit, PHPStan (level 8, 0 errors, 117 files analysed — up from 95), and PHPCS (hybrid ruleset, 0 errors/0 warnings across 210 files) — all clean after fixing the one real end-to-end test gap and two test-fixture mock-expectation gaps (see DECISIONS.md).

**Totals as of end of Phase 14:** adds 4 Modules (17 Module/Standard/Generator/ServiceProvider files: 3 each for MCP/Agent Skills/API Catalog + 8 for OAuth Discovery's module/2 generators beyond the module itself/3 standards/service provider) + 5 Http Controllers (22 new `app/` source files total) + 22 new test files (2 existing files extended: `RobotsScoringEndToEndTest`, `ApiRoutesTest`) to the Phase 13 count. No `Core/Scheduler.php`, database tables/migrations, Settings Manager, Logger, Cache Service, Queue, Commerce/Analytics/License/Updater modules, CI matrix, multisite validation pass, security/performance hardening pass, or packaging build exist yet — still deferred to Phase 15.

## Production source (`app/`) — Phase 15
| Module | Files | Owns a Standard? | Real content |
|---|---|---|---|
| Commerce | `app/Modules/Commerce/{CommerceModule,CommerceServiceProvider}.php`, `app/Http/Controllers/CommerceController.php` | No | `class_exists('WooCommerce')` (real, checkable) + honestly all-false AI-commerce capability declaration |
| Analytics | `app/Modules/Analytics/{AnalyticsModule,AnalyticsServiceProvider}.php`, `app/Http/Controllers/AnalyticsController.php` | No | 5 declared metrics, each honestly zero — no persisted counter store exists yet |
| License | `app/Modules/License/{LicenseModule,LicenseServiceProvider}.php`, `app/Http/Controllers/LicenseController.php` | No | Real current state: `tier: free`, `activated: false` |
| Updater | `app/Modules/Updater/{UpdaterModule,UpdaterServiceProvider}.php`, `app/Http/Controllers/UpdaterController.php` | No | Real current version, `channel: stable`, `update_available: false` |

## Tooling / infra — Phase 15
| File | Purpose |
|---|---|
| `.github/workflows/ci.yml` | Real, working CI: PHP quality (8.1–8.4 matrix), frontend quality, package build + verification — new for this project |
| `bin/build-release.sh` | Stages runtime-only files, runs `composer install --no-dev` in the staged copy, zips + checksums the result |
| `.project/RELEASE-GATE-CHECKLIST.md` | New — docs/28's Release Gates mapped to what was actually run/verified this session, including 3 honestly-documented non-blocking gaps |

## Modified this phase
| File | Change |
|---|---|
| `app/Core/Plugin.php` | `activate()`/`deactivate()` now accept WordPress's own `$networkWide` bool; `activate()` iterates `get_sites()` via `switch_to_blog()` when network-activated on real multisite |
| `app/Modules/ApiCatalog/ApiCatalogModule.php` | Route list extended with the 20 new Commerce/Analytics/License/Updater routes |
| `routes/api.php` | Adds `/commerce/*`, `/analytics/*`, `/license/*`, `/updater/*` (5 routes each, registered from one shared loop) |
| `composer.json` | `test`/`test:coverage` scoped to `--testsuite=Unit` (was accidentally unscoped); `test:integration` gained `--no-coverage` |
| `tests/Unit/Core/PluginTest.php` | +4 tests (network-wide activation, single-site and multisite; deactivate accepts `$networkWide`) |
| `tests/Unit/Routes/ApiRoutesTest.php` | Extended `moduleSlugs` with the 4 new platform modules |

## Tests (`tests/`) — Phase 15
Each of Commerce/Analytics/License/Updater has the same test shape as Headers (no Standard): `{X}ModuleSnapshotTest` (1), `{X}ModuleTest` (6), `{X}ServiceProviderTest` (3, including the no-Standard negative assertion), `{X}ControllerTest` (7) — 17 tests × 4 modules worth of files (16 new test files). Plus `tests/Integration/PackagingTest.php` (3 tests — the Integration testsuite's first real content ever).

**PHP: 546 Unit tests, 1118 assertions** (up from 475/958 at the end of Phase 14) + **3 Integration tests, 1263 assertions** (up from 0), confirmed by actually running PHPUnit, PHPStan (level 8, 0 errors, 129 files analysed — up from 117), and PHPCS (hybrid ruleset, 0 errors/0 warnings across 239 files). Frontend: `npm run build` + `npm run quality` re-verified green (unchanged this phase).

**Totals as of end of Phase 15:** adds 4 Modules (8 Module/ServiceProvider files) + 4 Http Controllers (12 new `app/` source files) + 17 new test files (4 existing files extended) + CI workflow + packaging script + release-gate checklist to the Phase 14 count. Remaining, explicitly deferred (see PROGRESS.md and RELEASE-GATE-CHECKLIST.md): a real WordPress install/activate smoke test, a code-coverage threshold gate, a full browser-based accessibility pass, `wpmu_new_blog` provisioning, `Core/Scheduler.php`, any `oxy_*` database table, Settings Manager, Logger/Cache/Queue services, real MCP transport, real OAuth Authorization Server, per-skill CRUD, live API-Catalog introspection.
