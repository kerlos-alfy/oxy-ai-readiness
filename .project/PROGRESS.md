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
