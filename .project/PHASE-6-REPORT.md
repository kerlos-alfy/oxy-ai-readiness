# Phase 6 Report — Generation Engine

**Date:** 2026-07-25.
**Status:** Complete, validated. Committed, tagged `phase-6`, pushed to GitHub autonomously.

## 1. Scope

Per `06-Phase-Plan.md` row 6: "Centralized generator framework, `GeneratorInterface`, publish/rollback/cache/version pipeline, `/generation/*` REST," exit criterion "A generated resource round-trips through Validation before publish; rollback restores prior version." No Scoring/Monitoring/Reporting engine, no database tables, no Settings Manager/Logger/Cache Service/Queue, no real user-facing Module, no admin UI.

## 2. A deliberate interface-scope deviation

`docs/17-Generation-Engine.md` literally lists `supports()`/`generate()`/`validate()`/`preview()`/`publish()`/`rollback()`/`cache()`/`version()` as methods every Generator implements. But the same document's Vision explicitly says "Instead of allowing every module to implement its own generation logic, the Generation Engine provides one centralized framework" — putting publish/rollback/cache/version on every Generator would force each one to reimplement identical file-versioning logic. `GeneratorInterface` was kept to `id()`/`resourceId()`/`supports()`/`generate()`; `GenerationService` owns the rest. Full reasoning in `.project/DECISIONS.md`.

## 3. What was implemented

| File | Purpose |
|---|---|
| `app/DTO/GenerationResult.php` | generatorId/path/version/checksum/publishedAt + `toArray()` |
| `app/Contracts/GeneratorInterface.php` | `id()`/`resourceId()`/`supports()`/`generate()` |
| `app/Exceptions/GenerationException.php` | Publish-with-failed-validation, publish-of-undiscovered-resource, write failure, nothing-to-roll-back |
| `app/Services/GenerationService.php` | registerGenerator/generate/preview/cache/publish/rollback/version/currentContent; `publish()` validates via `ValidationService` before writing; two-slot (current + `.previous`) versioning via `FileRepository` |
| `app/Http/Controllers/GenerationController.php` | `index`/`preview`/`publish`/`rollback`, `GenerationException` → HTTP 409 |
| `tests/Unit/Support/InMemoryFilesystem.php` | In-memory `WP_Filesystem_Base` test double for exercising multi-step file sequences without brittle Mockery call-pinning |

Modified: `app/Core/CoreServiceProvider.php` (binds `GenerationService`, constructing a `FileRepository` scoped to `storage/generated/`), `app/Core/Plugin.php` (`activate()` now calls `wp_mkdir_p()` — see §4), `app/Modules/Probe/ProbeModule.php` (now also `GeneratorInterface`), `app/Modules/Probe/ProbeServiceProvider.php` (registers the generator), `app/Modules/Probe/ProbeStandard.php` (`generate()` now delegates for real), `routes/api.php` (adds 4 Generation routes, one — `/generation/rollback` — beyond docs/17's own list; justified in §5/DECISIONS.md).

Tests: 2 new files (`GenerationServiceTest` 7, `GenerationControllerTest` 8) + 1 test-support file + 5 extended existing tests.

## 4. A real functional gap found and fixed

`FileRepository` (Phase 1) only ever creates directories *below* its configured base directory — its private `ensureDirectoryExists()` stops recursion and assumes the base directory itself already exists. Since no prior phase ever wrote to the filesystem, this was never exercised. Without a fix, `GenerationService::publish()` would fail on every fresh install, since `storage/generated/` (docs/04-Folder-Structure.md's designated location) has never been created. Fixed: `Plugin::activate()` now calls WordPress's `wp_mkdir_p()` to ensure it exists — the standard, idiomatic fix, cheaper than modifying Phase 1's already-shipped `FileRepository` for a concern specific to this one caller.

## 5. Checks performed — all run for real

- `composer validate` → valid.
- `composer test` → `OK (154 tests, 228 assertions)` — up from 135/190 at the end of Phase 5.
- `composer test:integration` → 0 tests (unchanged).
- PHPStan level 8 → `[OK] No errors` across 48 analysed files (up from 43).
- PHPCS (hybrid ruleset) → 0 errors, 0 warnings across 80 files (two long-line warnings and one multi-line-closure formatting error in the new tests, fixed by wrapping).
- `composer quality` → all green.

## 6. Documentation updates

None to `docs/*`. The `GeneratorInterface` scope deviation and the `/generation/rollback` route addition were both resolved as implementation decisions (logged in `DECISIONS.md`), not by editing docs/17 itself.

## 7. Decisions requiring your attention

Full detail in `.project/DECISIONS.md`. Most likely to need a second look:

- **`GeneratorInterface` omits `publish()`/`rollback()`/`cache()`/`version()`/`validate()`** from the literal per-generator method list, centralizing them in `GenerationService` instead — a documented architectural judgment call, not an oversight.
- **`POST /generation/rollback`** was added beyond docs/17's own REST list (which has `/generation/reset`, a different operation) since the exit criterion requires rollback to be reachable, and REST is meant to expose every capability per docs/24's own Success Criteria.
- **Versioning is two-slot (current + one previous), not a full history log** — sufficient for the exit criterion's "restores prior version" (singular), deferred to a DB-infra phase for anything fuller.

## 8. Files created/modified this phase

5 new `app/` files + 1 test-support file + 2 new test files + 6 modified production files + 5 extended test files + `.project/PROGRESS.md`, `.project/DECISIONS.md`, `.project/FILE-MANIFEST.md` updated, this report new.

## 9. What's explicitly still missing (by design — later phases)

Full version history, Scoring/Monitoring/Reporting engines, `Core/Scheduler.php`, any custom `oxy_*` database table or migration, Settings Manager, Logger service, a proper Cache Service, Queue, any real user-facing Module, docs/17's `oxy_ai_generation_*` filters, custom capability registration, any admin UI, `package.json`/frontend tooling.

## 10. Git

Committed as "Phase 6: Generation Engine," tagged `phase-6`, pushed to `origin/main` along with the tag.

---

**Phase 6 complete. Continuing directly to Phase 7 per the user's standing autonomous-mode authorization.**
