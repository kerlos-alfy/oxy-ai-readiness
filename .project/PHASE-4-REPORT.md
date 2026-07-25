# Phase 4 Report — Discovery Engine

**Date:** 2026-07-25.
**Status:** Complete, validated. Committed, tagged `phase-4`, and pushed to GitHub autonomously, per the user's standing authorization for this session (approval required again only for Phase 5+ or the listed destructive/out-of-repo actions).

## 1. Scope

Per `06-Phase-Plan.md` row 4: "Resource/file/header/endpoint discovery pipeline → Discovery Map, `/discovery/*` REST," exit criterion "Discovery Map correctly lists a known fixture resource; read-only, no writes." No database tables, no migrations, no Settings Manager/Logger/Cache Service/Queue, no real user-facing Module, no Validation/Generation/Scoring/Monitoring/Reporting engine logic, no mutating REST route, no admin UI.

## 2. A real conflict found before writing code

`docs/14-Discovery-Engine.md`'s own REST API section (`GET /discovery`, `/discovery/map`, `/discovery/resources`, `/discovery/modules`; `POST /discovery/scan`, `/discovery/reset`) disagrees with `docs/24-REST-API-Spec.md`'s Discovery API section (`GET /discovery`, `/discovery/files`, `/discovery/resources`; `POST /discovery/run`). `.project/adr/ADR-003-rest-api-naming.md` never caught this — it explicitly verified route *prefixing* only ("Discovery... were already correctly prefixed and are unchanged"), not cross-document route-*naming* agreement.

Resolved by treating `docs/14` (the dedicated engine spec, written with full context of its own "Discovery Map"/pipeline terminology) as authoritative, and — per the exit criterion's explicit "read-only, no writes" — building only the GET routes both documents' route sets have in common in spirit (`/discovery`, `/discovery/resources`, plus `docs/14`'s `/discovery/map`), while deferring either document's POST route entirely rather than guessing between `/scan` and `/run`. Full reasoning in `.project/DECISIONS.md`.

## 3. What was implemented

| File | Purpose |
|---|---|
| `app/DTO/DiscoveredResource.php` | Discovery Map entry: id/type/location/status/version/module/health/dependencies/source/lastChecked (docs/14's field list) + `toArray()` |
| `app/Contracts/DiscoveryInterface.php` | Per-module Discovery provider contract (docs/22-Plugin-SDK.md's SDK Interfaces list) |
| `app/Services/DiscoveryService.php` | The engine: `registerProvider`/`scan`/`map`/`resources`/`reset` — in-memory, lazy-scan on first access, fires `oxy_ai_discovery_started`/`oxy_ai_resource_discovered`/`oxy_ai_discovery_finished` |
| `app/Core/RestServiceProvider.php` | First REST wiring: hooks `rest_api_init` via the (now Container-bound) `Hooks` registrar, loads `routes/api.php` |
| `app/Http/Controllers/DiscoveryController.php` | `index`/`map`/`resources` — all GET, gated by `current_user_can('manage_options')` |
| `routes/api.php` | Registers the three GET routes under `oxy-ai/v1` |

Modified: `app/Core/CoreServiceProvider.php` (binds `DiscoveryService`), `app/Modules/Probe/ProbeModule.php` (now also implements `DiscoveryInterface`, returning one fixture `DiscoveredResource` — the exit criterion's "known fixture resource," reusing Phase 3's established probe pattern rather than inventing a new one), `app/Modules/Probe/ProbeServiceProvider.php` (registers the same probe instance with `DiscoveryService`), `app/Core/Plugin.php` (`Hooks` now a Container singleton shared with `Kernel`; `RestServiceProvider` added to the provider list), `tests/stubs/wp-core-stubs.php` (added `WP_REST_Request`/`WP_REST_Response` stand-ins), `phpstan.neon`/`phpcs.xml` (added `routes/`).

Tests: 4 new files (`DiscoveryServiceTest` 5, `DiscoveryControllerTest` 4, `RestServiceProviderTest` 2, `ApiRoutesTest` 1) + 3 extended (`CoreServiceProviderTest`, `ProbeModuleTest`, `ProbeServiceProviderTest`).

## 4. Checks performed — all run for real

- `composer validate` → valid.
- `composer test` → `OK (123 tests, 165 assertions)` — up from 109/142 at the end of Phase 3.
- `composer test:integration` → 0 tests (unchanged).
- PHPStan level 8 → `[OK] No errors` across 38 analysed files (up from 32 — confirms `routes/` was actually added to scope, not silently skipped the way `app/Modules/*` was in Phase 3).
- PHPCS (hybrid ruleset) → 0 errors, 0 warnings across 65 files.
- `composer quality` → all green.

One real PHPStan finding, fixed properly rather than suppressed: `DiscoveryService::map()` returned a property PHPStan couldn't narrow to non-nullable across the `scan()` call boundary. Fixed with `return $this->map ?? [];` — type-honest, not an `@phpstan-ignore` workaround.

## 5. Documentation updates

None to `docs/*` — the Discovery route-naming conflict (§2) was resolved as an implementation decision (logged in `DECISIONS.md`), not by editing the docs themselves, since it's genuinely ambiguous which of the two conflicting names is "correct" without the user's input — safer to flag it than to silently canonicalize one doc over the other in the source of truth.

## 6. Decisions requiring your attention

Full detail in `.project/DECISIONS.md`. Most likely to need a second look:

- **The Discovery REST route-naming conflict (§2).** Whichever of `POST /discovery/scan` or `POST /discovery/run` is eventually needed should be settled explicitly, not inferred from this phase's silence on it.
- **`manage_options` as the interim REST capability gate.** Should be swapped for a real registered capability (`manage_oxy`/`view_audit`) once a Permissions/capability-registration system exists.
- **No mutating Discovery REST route yet**, by design — deferred until rate-limiting/audit-logging infrastructure exists, per docs/24/26's API Security requirements.

## 7. Files created/modified this phase

6 new `app/`-or-root files (1 DTO, 1 Contract, 1 Service, 1 Core ServiceProvider, 1 Http Controller, 1 routes file) + 4 new test files + 3 extended test files + 4 modified production files + 2 modified tooling configs + 1 modified test-stub file + `.project/PROGRESS.md`, `.project/DECISIONS.md`, `.project/FILE-MANIFEST.md` updated, this report new.

## 8. What's explicitly still missing (by design — later phases)

`POST /discovery/scan` (or `/run`), `Core/Scheduler.php`, any custom `oxy_*` database table or migration, Settings Manager, Logger service, Cache Service, Queue, any real user-facing Module, Validation/Generation/Scoring/Monitoring/Reporting engines, custom capability registration, rate limiting, audit logging, any admin UI, `package.json`/frontend tooling.

## 9. Git

Committed as "Phase 4: Discovery Engine," tagged `phase-4`, pushed to `origin/main` along with the tag.

---

**Phase 4 complete. Per your instructions, stopping here — Phase 5 requires your explicit approval.**
