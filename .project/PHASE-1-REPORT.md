# Phase 1 Report — Repository Foundation

**Date:** 2026-07-24 (original build); validated for real the same day once local PHP/Composer/Node/npm/Git became available.
**Status:** Complete and validated. Awaiting user approval before Phase 2.

## 0. Validation pass addendum — read this first

Everything in §3 below ("Checks performed") describes what was possible *before* PHP was available in this environment — manual review only, nothing executed. That has since been superseded: `composer install`, `composer validate`, `composer test`, PHPUnit, PHPStan, and PHPCS were all run for real, using `C:\php\php.exe` as the canonical PHP executable (the `C:\xampp\php` install was explicitly excluded per instruction). Full log in `TEST-STATUS.md`; summary:

- **All checks pass, exit code 0:** `composer install` (39 packages), `composer validate`, `composer test` → `OK (54 tests, 57 assertions)`, PHPStan level 8 → `[OK] No errors`, PHPCS → 0 errors/0 warnings, `composer quality`.
- **Three environment fixes** were needed before anything would run at all (none are Phase 1 code defects — see `TEST-STATUS.md` for the full table): `php_zip.dll` was present but disabled in `php.ini`; PHPStan's default 128M memory limit was too low (ran with `--memory-limit=512M`); `phpcs.xml` referenced the `PHPCompatibilityWP` sniff without its Composer package ever having been added (added `phpcompatibility/phpcompatibility-wp` to `require-dev`).
- **One real bug found and fixed**, by PHPStan: `FileRepository::write()` called `$fs->mkdir($directory, 0755, true)`, treating the third argument as a "recursive" flag the way PHP's native `mkdir()` works. `WP_Filesystem_Base::mkdir()`'s actual signature is `mkdir($path, $chmod, $chown, $chgrp)` — there is no recursive option, so `true` was silently being passed as `$chown`, and any directory more than one level below the configured base would never actually have been created. Fixed with a recursive `ensureDirectoryExists()` helper walking up to `$baseDir`; the corresponding test's mock assertion (`FileRepositoryTest::test_write_creates_directory_writes_temp_file_then_moves_it_atomically`) was updated to match the corrected 2-argument `mkdir()` call.
- **PHPCS ruleset was a genuine conflict, resolved by asking the user, not unilaterally.** The originally-committed `phpcs.xml` used a bare `WordPress-Extra` ruleset, which produced **1,877 errors across all 15 files** — almost entirely the ruleset's classic-procedural-WordPress formatting rules (tabs, snake_case variables/properties, K&R brace placement, PEAR-style parenthesis spacing) fighting the actual Phase 1 code, which was written in the style `docs/29-Developer-Guide.md` itself documents ("PSR-12 where compatible"; Naming Conventions: Methods camelCase; Typed/Readonly Properties). This had never surfaced before because PHPCS had never actually run. I asked the user how to reconcile it rather than guessing; they chose a hybrid ruleset — PSR-12 governs formatting/naming, `WordPress.Security`/`WordPress.WP`/`WordPress.DB`/`WordPressVIPMinimum.Security` are kept for WordPress-specific security and API-usage sniffs (added `automattic/vipwpcs` to `require-dev` for the VIP-minimum security sniffs). After that change, the count dropped to 64 errors/2 warnings, all of which were then individually triaged (see `TEST-STATUS.md` and `DECISIONS.md`): PHPUnit's `test_snake_case` method-naming convention, `tests/stubs/wp-core-stubs.php` needing to mirror real (non-namespaced, snake_case) WordPress core class/method names, `sprintf()`-built exception messages incorrectly flagged by an output-escaping sniff, one line over 120 characters, and one warning on a test that's deliberately exercising the 100-row clamp with an out-of-range input (5000) — all resolved down to a clean 0/0.
- **Added `tests/Integration/`** (empty, `.gitkeep` placeholder only). `phpunit.xml.dist` declared an `Integration` testsuite whose directory didn't exist, which makes PHPUnit fail at configuration-parse time on any invocation without `--testsuite=Unit` (including the plain `phpunit`/`composer test` used here). No integration tests were written — still explicitly out of scope for Phase 1.
- **Known gap, flagged not hidden:** no code-coverage driver (Xdebug/PCOV) is installed on this PHP build, so the coverage thresholds in `docs/28-Testing-Strategy.md` (80% minimum, 90–95% for core/security/scoring/auto-fix/REST) aren't measurable yet. `composer.json`'s `test` script now runs `phpunit --no-coverage` explicitly (that's why the checks above pass); a `test:coverage` script exists for once a driver is installed.

## 1. Scope

Per the user's explicit instruction, Phase 1 was scoped narrowly to **only** the Repository Foundation: the Repository Pattern layer described in `docs/02-Architecture.md` ("Service → Repository → WordPress, never Service → WP_Query") and the five shared repositories named in `docs/04-Folder-Structure.md` (canonical, post-ADR-002): `OptionsRepository`, `PostRepository`, `FileRepository`, `TransientRepository`, `UserRepository`, plus the `RepositoryInterface` contract they all implement.

No Service Container, no plugin bootstrap, no Modules, no Standards, no REST layer, no custom database tables/migrations, and no frontend tooling were implemented — those belong to later phases and were explicitly out of scope ("do not implement Phase 2 or later").

## 2. What was implemented

### Tooling (repo root)
`composer.json` (PSR-4 `OxyAI\` → `app/`, dev deps: PHPUnit 10, Brain Monkey, Mockery, PHPStan level 8 + WordPress stubs, WPCS), `phpcs.xml`, `phpstan.neon`, `phpunit.xml.dist`.

### Contract
`app/Contracts/RepositoryInterface.php` — an empty marker interface (see Decisions §1 for why it has no required methods).

### The five repositories (`app/Repositories/`)
| Repository | Wraps | Key safety behavior |
|---|---|---|
| `OptionsRepository` | `get_option`/`update_option`/`delete_option` | `oxy_ai_` key prefix + format validation; autoload defaults to `false` |
| `TransientRepository` | `get_transient`/`set_transient`/`delete_transient` | Key prefix + length validation; `remember()` cache-aside helper; TTL cannot be negative |
| `UserRepository` | `get_userdata`/`get_user_by`/`current_user_can`/`user_can` | Normalizes `WP_User` → plain array, keeping `WP_User` out of the Application/Domain layers |
| `PostRepository` | `get_post`/`get_posts`/`wp_count_posts` | Rejects unbounded queries (`posts_per_page = -1`), clamps to a 100-row max per `docs/27-Performance-Spec.md` |
| `FileRepository` | `WP_Filesystem_Base` (injected) | Path confinement (rejects `..`, absolute paths, null bytes), atomic write-then-move, SHA-256 checksums, per `docs/26-Security-Spec.md` |

### Tests
`tests/bootstrap.php`, `tests/stubs/wp-core-stubs.php` (minimal local `WP_User`/`WP_Post`/`WP_Filesystem_Base` stand-ins), `tests/Unit/TestCase.php` (Brain Monkey wiring), and 6 test classes totaling **43 test methods**: happy path, at least one validation/rejection case, and at least one edge case (cache-hit-skips-callback, move-failure-cleans-up-temp-file, etc.) per repository, plus a contract-conformance test verifying every repository implements `RepositoryInterface` and is declared `final`.

## 3. Checks performed — read this before trusting anything above

**This sandbox has no PHP, Composer, Node, npm, or git binary available** (verified with `Get-Command`, all five returned "not found"). This means:

- `composer install` was **not** run — the `vendor/` directory does not exist, so `phpunit`, `phpstan`, and `phpcs` are not actually runnable here.
- **No test in this Phase 1 was actually executed.** I cannot claim "43 tests pass" — I can only claim 43 tests were written and manually traced for correctness.
- What I did instead, as the most rigorous substitute available without a PHP interpreter:
  1. Re-read every one of the 15 new PHP files in full after writing them and checked each one manually against its governing doc section.
  2. Ran an automated brace/parenthesis balance check (PowerShell regex count) across all 15 files — all 15 balanced (see raw output in `PROGRESS.md`).
  3. Manually traced every test's Mockery/Brain-Monkey expectation sequence against the exact call sequence in the corresponding repository method to confirm mock expectations match real behavior (argument order, counts, return values).

**Action needed from you (or a future session with real tooling):** run `composer install && composer quality` (lint + analyse + test) in an environment with PHP 8.1+ and Composer before this code is trusted as a working foundation for Phase 2. I'm flagging this as the top risk of this phase rather than glossing over it.

## 4. Documentation updates

None required beyond what Phase 0.5 already established — Phase 1 implemented against the already-canonical docs without discovering new conflicts.

## 5. Decisions requiring your attention

Full detail in `.project/DECISIONS.md`. The one most likely to need your input:

- **I created `composer.json`/`phpcs.xml`/`phpstan.neon`/`phpunit.xml.dist`.** Phase 0.5 had an explicit instruction not to create `composer.json`; I judged that restriction as scoped to that phase specifically, and that Phase 1 (real implementation) needs minimal autoloading/tooling config to exist and be checkable at all. I deliberately did **not** create the plugin bootstrap file, `uninstall.php`, `readme.txt`, or `package.json` — if you intended Phase 1 to exclude even this minimal tooling, let me know and I'll adjust.

## 6. Files created this phase

19 files: 4 tooling configs, 1 contract, 5 repositories, 9 test-related files. Full manifest in `.project/FILE-MANIFEST.md`.

## 7. What's explicitly still missing (by design — later phases)

Service Container, plugin bootstrap (`oxy-ai-readiness.php`), `Core/` classes, `ModuleRegistry`/`StandardsRegistry`, any custom `oxy_*` database table or migration, any Module, any Standard, any REST endpoint, any admin UI. The Repository Foundation is deliberately usable in isolation (each repository can be `new`'d directly with no container) so it doesn't block on any of the above.

## 8. Recommendation for Phase 2

Per the previously-approved draft plan (`.project/06-Phase-Plan.md`), Phase 2 would be "Database & shared infrastructure" (migration runner, `oxy_settings`/`oxy_modules`/`oxy_standards` tables, Settings Manager, Logger, Cache Service, Queue skeleton). Since you're defining phases explicitly rather than following that draft verbatim, I'll wait for your Phase 2 scope instruction rather than assume.

---

**Stopping here. Waiting for approval before Phase 2.**
