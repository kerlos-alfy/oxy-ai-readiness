# Test Status

Canonical record of what was actually executed, when, and with what result. Supersedes the "no PHP available, checks not run" caveat in the original `PHASE-1-REPORT.md` / `PROGRESS.md` — this is a real run.

## Environment

- **PHP:** `C:\php\php.exe`, PHP 8.5.8 (per user instruction: canonical executable; the `C:\xampp\php` install exists but was explicitly excluded from use)
- **Composer:** `C:\ProgramData\ComposerSetup\bin\composer.phar`, invoked via `C:\php\php.exe`
- **Date executed:** 2026-07-24

### Environment fixes required before anything would run

None of these are Phase 1 code defects — they were local PHP/tooling configuration gaps that blocked execution entirely. Documented here so a future environment rebuild doesn't hit the same wall silently.

| Problem | Fix | File touched |
|---|---|---|
| `composer install` failed: "The zip extension and unzip/7z commands are both missing" | `php_zip.dll` was present in `C:\php\ext` but commented out. Uncommented `extension=zip` in `C:\php\php.ini`. | `C:\php\php.ini` (outside repo) |
| `phpunit`/`phpstan` invoked via `composer <script>` failed with `'php' is not recognized` | Composer's generated `vendor/bin/*.bat` shims call `php` bare, not by full path. Added `C:\php` to `PATH` for the shell session before invoking composer scripts. | none (session-only) |
| `phpstan analyse` crashed: "reached configured PHP memory limit: 128M" | `php.ini`'s default 128M is too low for PHPStan + WordPress stubs. Ran with `--memory-limit=512M` (CLI flag, not a global php.ini change). | none (CLI flag only) |
| `phpcs` failed: `ERROR: Referenced sniff "PHPCompatibilityWP" does not exist` | `phpcs.xml` referenced `PHPCompatibilityWP` but `phpcompatibility/phpcompatibility-wp` was never added as a Composer dependency. Added it to `require-dev`. | `composer.json` |

## Results — final clean run

All four commands below were run from repo root with `C:\php` prepended to `PATH` for that session:

| Check | Command | Result |
|---|---|---|
| `composer install` | `php composer.phar install --no-interaction` | ✅ 39 packages installed, no errors |
| `composer validate` | `php composer.phar validate --no-check-all` | ✅ `./composer.json is valid` (the "could not detect root package version" line is a benign notice — no git tags exist yet, not an error) |
| `composer test` | `php composer.phar test` (→ `phpunit --no-coverage`) | ✅ `OK (54 tests, 57 assertions)` |
| PHPUnit (direct) | `php vendor/bin/phpunit --no-coverage` | ✅ `OK (54 tests, 57 assertions)` |
| PHPStan | `php vendor/bin/phpstan analyse --memory-limit=512M` | ✅ `[OK] No errors` (level 8, scoped to `app/`) |
| PHPCS | `php vendor/bin/phpcs` | ✅ 0 errors, 0 warnings |
| `composer quality` (lint + analyse + test) | `php composer.phar quality` | ✅ all three sub-steps pass |

**Exit code on every command above: 0.**

### Test breakdown (54 tests, 57 assertions)

| Test file | Test methods | Notes |
|---|---|---|
| `RepositoryContractTest.php` | 2 methods × 5 repositories (`@dataProvider`) = 10 cases | Every repository implements `RepositoryInterface` and is declared `final` |
| `OptionsRepositoryTest.php` | 8 | |
| `TransientRepositoryTest.php` | 8 | |
| `UserRepositoryTest.php` | 7 | |
| `PostRepositoryTest.php` | 8 | |
| `FileRepositoryTest.php` | 13 | |
| **Total** | **54** | (46 declared `test_*` methods; `RepositoryContractTest`'s 2 are multiplied ×5 by its data provider) |

Note: `PHASE-1-REPORT.md`/`PROGRESS.md` originally estimated "43 test methods" from manual reading before execution was possible — the actual count is 46 declared methods / 54 executed cases. Corrected here rather than silently left inconsistent.

### Coverage — not measured, environment gap (not a Phase 1 gate)

No code-coverage driver (Xdebug or PCOV) is installed on `C:\php`. Requesting HTML/text coverage on this PHP build crashes with "No code coverage driver available" (a PHPUnit runtime warning that fails the run under this config). Fix applied: `composer.json`'s `test` script now runs `phpunit --no-coverage` explicitly; a new `test:coverage` script (`phpunit`, no flag) is available for when a driver is installed.

`docs/28-Testing-Strategy.md` sets coverage thresholds (80% overall minimum, 90–95% for core/security/scoring/auto-fix/REST code) — none of that is enforceable in this environment right now. **Flagged as a risk**, not silently passed over: install `pcov` (lighter/faster) or `xdebug` in mode `coverage` before coverage thresholds can be gated in CI or trusted for any later phase touching security/scoring/auto-fix code.

### PHPCS ruleset — hybrid standard (see `DECISIONS.md` for full rationale)

`phpcs.xml` was rewritten from a bare `WordPress-Extra` ruleset (1,877 errors against the actual Phase 1 code — almost entirely tabs-vs-spaces, snake_case-vs-camelCase, and brace-placement conflicts with the docs' own "PSR-12 where compatible" + explicit camelCase-methods convention) to a hybrid: **PSR-12 governs formatting/naming**; `WordPress.Security`, `WordPress.WP`, `WordPress.DB`, and `WordPressVIPMinimum.Security` are kept for WordPress-API-usage and security sniffs. This was a user decision (asked and confirmed mid-session), not a unilateral call — see `DECISIONS.md` entry for 2026-07-24.

Three narrow, justified exclusions remain on top of that hybrid (all scoped by path or specific error code, not blanket-disabled):
- `PSR1.Methods.CamelCapsMethodName.NotCamelCaps` excluded under `*/tests/*` — PHPUnit's `test_snake_case_description` convention.
- `PSR1.Classes.ClassDeclaration.{MissingNamespace,MultipleClasses}` and `Squiz.Classes.ValidClassName.NotCamelCaps` excluded under `tests/stubs/*` — that file stands in for real WordPress core classes (`WP_User`, `WP_Post`, `WP_Filesystem_Base`) and must mirror their actual non-namespaced, snake_case-method API.
- `WordPress.Security.EscapeOutput.ExceptionNotEscaped` excluded project-wide — every hit was `sprintf()` building an exception message, never browser output; real output-escaping sniffs (`OutputNotEscaped` etc.) remain active.

## Code fix made during this validation pass

`app/Repositories/FileRepository.php`'s `write()` called `$fs->mkdir($directory, 0755, true)`, passing `true` as the third argument intending "recursive". PHPStan (level 8) caught this: `WP_Filesystem_Base::mkdir()`'s real signature is `mkdir($path, $chmod = false, $chown = false, $chgrp = false)` — there is no recursive flag (unlike PHP's native `mkdir()`), so `true` was being passed as `$chown`, and nested directories would never actually have been created. Replaced with a recursive `ensureDirectoryExists()` helper that walks up to `$baseDir` (assumed pre-existing) creating one level at a time via `$fs->mkdir($dir, 0755)`. `FileRepositoryTest.php`'s mock expectation was updated to match (`mkdir` called with 2 args, not 3). This was a real logic bug, not a style issue — it would have silently failed to create any output directory more than one level below the configured base.

## What was NOT changed

Repository logic in `OptionsRepository`, `TransientRepository`, `UserRepository`, `PostRepository`, and `RepositoryInterface` — no findings against any of them beyond the resolved tooling/ruleset issues above.
