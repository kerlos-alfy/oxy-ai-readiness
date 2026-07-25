# Decision Log

Append-only record of non-obvious decisions made during implementation, with rationale, per CLAUDE.md Mandatory Workflow step 8. No implementation decisions have been made yet — this file will accumulate entries starting in Phase 1.

## Format for future entries

```
## YYYY-MM-DD — <short decision title>
**Decision:** what was decided.
**Context:** why a decision was needed (which doc/conflict/question triggered it).
**Rationale:** why this option over the alternatives.
**Affects:** files/modules/phases impacted.
```

## 2026-07-24 — Phase 0 baseline

No implementation decisions yet. Phase 0 surfaced 4 blocking questions (see `04-Questions.md`) that must be answered before Phase 1 decisions can be logged here.

## 2026-07-24 — Phase 0.5: Architecture Normalization (5 decisions)

**Decision:** Standards are metadata/lifecycle wrappers owned by one Module each, delegating to that module's engine registrations; no `app/Standards/` top-level directory.
**Context:** `05-Modules.md` and `23-AI-Standards-Layer.md` defined overlapping ownership of the same AI specs.
**Rationale:** Avoids duplicate discover/generate/validate/score/monitor/report logic; keeps the Standards Registry useful (version negotiation, deprecation, compat matrix) without becoming a second implementation surface.
**Affects:** `docs/05-Modules.md`, `docs/23-AI-Standards-Layer.md`, `docs/29-Developer-Guide.md`, all future module scaffolding (Phase 3+). Full detail: `.project/adr/ADR-001-modules-vs-standards.md`.

**Decision:** `docs/29-Developer-Guide.md`'s module folder structure is canonical, with `{Module}Standard.php` and `Repositories/` added, `Views/`/`Assets/` removed.
**Context:** Three incompatible module folder templates existed across 04/22/29.
**Rationale:** 29 is the only variant backed by working code examples that the rest of the doc set already depends on.
**Affects:** `docs/04-Folder-Structure.md`, `docs/22-Plugin-SDK.md`, `docs/29-Developer-Guide.md`, Phase 1/3 scaffolding. Full detail: `.project/adr/ADR-002-folder-structure.md`.

**Decision:** Every REST route must include its module/engine slug as its first path segment; no bare routes.
**Context:** `24-REST-API-Spec.md`'s Monitoring/Reporting sections listed unprefixed paths that would collide with other modules.
**Rationale:** Prevents route collisions; matches the convention every other module doc already follows.
**Affects:** `docs/24-REST-API-Spec.md`, Phase 10 REST wiring. Full detail: `.project/adr/ADR-003-rest-api-naming.md`.

**Decision:** `oxy_settings.key`/`value` renamed to `setting_key`/`setting_value`; formal DB naming convention adopted.
**Context:** `key` is a MySQL/MariaDB reserved word.
**Rationale:** Avoids mandatory backtick-escaping landmine; establishes a convention future tables (Phase 2+) must follow.
**Affects:** `docs/25-Database-Schema.md`, Phase 2 migrations. Full detail: `.project/adr/ADR-004-database-naming.md`.

**Decision:** The 10-band letter-grade scale in `15-Scoring-Engine.md`'s GRADE SYSTEM is canonical; `06-Audit-Engine.md`'s 5 labels are remapped onto it; the redundant "AI READINESS LEVELS" section is removed.
**Context:** Three non-aligned score-band scales existed (06's 5-band, 15's 10-band grades, 15's separate 6-band levels).
**Rationale:** The grade boundaries already match `28-Testing-Strategy.md`'s hard-coded boundary test values, making them the de facto load-bearing scale.
**Affects:** `docs/06-Audit-Engine.md`, `docs/15-Scoring-Engine.md`, Phase 7 Scoring Engine implementation. Category weights explicitly left unchanged (out of scope; see Question 11). Full detail: `.project/adr/ADR-005-scoring-grading.md`.
