# Phase 13 Report — Monitoring + Reporting Engines

**Date:** 2026-07-25.
**Status:** Complete, validated. Committed, tagged `phase-13`, pushed to GitHub autonomously.

## 1. Scope

Per `06-Phase-Plan.md` row 13: "Change detection, notifications, report generation/export/sharing," depending on Phase 9 (Audit) and Phase 7 (Scoring), exit criterion "A simulated resource change triggers revalidation + notification; a report exports in at least one format."

## 2. What was implemented

**Monitoring Engine** — `app/Services/MonitoringService.php`. Follows docs/20-Monitoring-Engine.md's own pipeline (Scheduler → Resource Scanner → Change Detection → Validation Engine → Impact Analysis → Notifications), with one honest gap: no `Core/Scheduler.php` exists yet (every phase report since Phase 2 has listed it as still missing), so there's no automatic timer. `start()` arms monitoring and takes a baseline "fingerprint" of every currently-discovered resource — a hash of its Discovery Map metadata plus its generated content where a Generator is registered for its module. `scan()`, triggered manually via `POST /monitoring/scan`, diffs the current state against that baseline: anything new, changed, or missing is immediately revalidated through the existing `ValidationService` and turned into a `MonitoringEvent`, firing `oxy_ai_resource_changed` and (unless the change is purely informational) `oxy_ai_notification_sent`. This is a direct, literal implementation of the exit criterion's "a simulated resource change triggers revalidation + notification" — tests simulate the change by mocking a `DiscoveryInterface`/`GeneratorInterface` pair that returns different output across two successive calls.

**Reporting Engine** — `app/Services/ReportService.php`. Follows docs/21-Reporting-Engine.md's pipeline (Data Sources → Normalize → Aggregate → Analyze → Generate → Export). `generate()` runs a real `AuditService` scan, derives `Recommendation`s from its results via the existing `RecommendationService`, and folds in whatever `MonitoringService` has observed so far, into a `Report` DTO — docs' own "Technical Report" shape, the one report type buildable entirely from real, already-computed data. `export()` renders that report as JSON (its own `toArray()`) or Markdown (a real multi-section rendering: score, validation results, recommendations, monitoring events) — both fully working, satisfying "exports in at least one format" with margin.

Both wired as `CoreServiceProvider` singletons and exposed via `MonitoringController` (`/monitoring`, `/monitoring/status`, `/monitoring/events`, `/monitoring/start`, `/monitoring/stop`, `/monitoring/reset`, `/monitoring/scan`) and `ReportController` (`GET /reports`, `POST /reports/generate`, `POST /reports/export`).

## 3. Real content, not fabricated data

Every signal `MonitoringService` reacts to comes from data engines already produce for real: Discovery Map metadata (Phase 4), generated content (Phase 6), validation outcomes (Phase 5) — no synthetic "health score" or invented telemetry. `ReportService`'s Markdown export is a genuinely readable document built from the same real `AuditReport`/`Recommendation`/`MonitoringEvent` data its JSON export carries, not a placeholder template with blanks. Neither engine invents business-summary copy, branding, or compliance mappings it has no real source for.

## 4. A real bug caught and fixed before it became a false-positive machine

An early draft of the fingerprint function hashed a resource's full Discovery Map entry, including `lastChecked`. Since `RobotsModule::discover()` (and every other module) sets `lastChecked` to `gmdate('c')` on every single `discover()` call, this would have made *every* resource look "changed" on *every* scan — a 100% false-positive rate, silently defeating the entire point of change detection. Caught before writing tests by re-reading `DiscoveredResource`'s own field semantics; `lastChecked` is now explicitly excluded from the fingerprint, documented in `DECISIONS.md`.

## 5. Checks performed — all run for real, clean on the first pass

`composer validate` → valid. `composer test` → `OK (369 tests, 701 assertions)` — up from 338/622 at the end of Phase 12. `composer test:integration` → 0 tests (unchanged, pre-existing since Phase 1; not part of `composer quality`). PHPStan level 8 → `[OK] No errors` across 95 analysed files (up from 86). PHPCS (hybrid ruleset) → 0 errors, 0 warnings across 166 files. `composer quality` → all green. `npm run quality` re-verified green (frontend untouched this phase).

## 6. Documentation updates

None to `docs/*`.

## 7. Decisions requiring your attention

Full detail in `.project/DECISIONS.md`. Most likely to need a second look:

- **No automatic Scheduler trigger** — `scan()` is manually invoked via REST; a real Scheduler (not built yet) would call it on a timer once it exists.
- **`ChangeType`/`NotificationPriority` only partially populated** — same "real enum, not every case reachable yet" precedent as Phase 9's `ScanType`.
- **Only one Report type (Technical) and two export formats (JSON/Markdown)** — Executive/Agency/White-Label/Compliance report types and PDF/Excel/ZIP formats need content sources and libraries this project doesn't have.
- **`/monitoring/history` and four `/reports/*` routes not implemented** — each would need persisted storage, templating, or external delivery infrastructure that doesn't exist.

## 8. Files created/modified this phase

New: 5 `app/DTO/*` files, 2 `app/Services/*` files, 2 `app/Http/Controllers/*` files, 4 new test files. Modified: `app/Core/CoreServiceProvider.php`, `routes/api.php`, `tests/Unit/Core/CoreServiceProviderTest.php`, `tests/Unit/Routes/ApiRoutesTest.php`. `.project/PROGRESS.md`, `.project/DECISIONS.md`, `.project/FILE-MANIFEST.md` updated, this report new.

## 9. What's explicitly still missing (by design — later phases)

`Core/Scheduler.php`; any custom `oxy_*` database table or migration; Settings Manager, Logger service, Cache Service, Queue; `/monitoring/history`; `/reports/history`, `/reports/templates`, `/reports/share`, `DELETE /reports/cache`; Executive/Agency/White-Label/Compliance/Security/Performance report types; Slack/Discord/Teams/Telegram/Webhook/Push notification channels; PDF/Excel/CSV/XML/ZIP/HTML export formats; `ChangeType::Broken/Deprecated/Moved/Redirected/Disabled/Expired`; `NotificationPriority::High/Low`; MCP/Agent Skills/API Catalog/OAuth Discovery modules.

## 10. Git

Committed as "Phase 13: Monitoring + Reporting Engines," tagged `phase-13`, pushed to `origin/main` along with the tag.

---

**Phase 13 complete. Per the user's instruction, stopping here to wait for approval before Phase 14.**
