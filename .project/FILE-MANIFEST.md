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
