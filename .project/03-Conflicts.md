# Conflicts

Contradictions and ambiguities found across the documentation set during Phase 0 analysis. **All architecture-level conflicts (§2–§6) were resolved in Phase 0.5 — Architecture Normalization**, via the ADRs in `.project/adr/`. §1 and §7 remain open (see notes).

## 1. `docs/30-Claude-Code-Master.md` does not contain a Master Execution Contract — OPEN

Still unresolved. `CLAUDE.md` and `docs/30-Claude-Code-Master.md` remain byte-identical; no doc-sourced phase breakdown exists beyond "Phase 0". This is a process/governance question, not an architecture conflict, so it was out of scope for Phase 0.5. It is resolved by explicit user approval of `.project/06-Phase-Plan.md` (Claude's proposal) rather than by a documentation edit. Still blocking before Phase 1 begins.

## 2. Three incompatible "canonical" module folder structures — **RESOLVED (ADR-002)**

`docs/29-Developer-Guide.md`'s structure was adopted as canonical, with a `{Module}Standard.php` addition (ADR-001) and `Repositories/` promoted to a first-class subfolder. `docs/04-Folder-Structure.md` and `docs/22-Plugin-SDK.md` were updated in place to match. Per-module `Views/`/`Assets/` folders were removed — the admin UI is a centralized React SPA (`assets/react/`), not per-module server-rendered views.

See `.project/adr/ADR-002-folder-structure.md`.

## 3. AI Standards Layer vs Modules — unclear ownership boundary — **RESOLVED (ADR-001)**

A Module is the WordPress integration shell; a Standard is a metadata/lifecycle descriptor owned by exactly one Module, delegating to that module's already-registered Generator/Validator/ScoreProvider/Monitor/Reporter rather than re-implementing engine logic. `app/Standards/` does not exist as a top-level directory. An explicit Module→Standard ownership table was added to `docs/23-AI-Standards-Layer.md`. `docs/05-Modules.md`, `docs/23-AI-Standards-Layer.md`, and `docs/29-Developer-Guide.md` were updated.

See `.project/adr/ADR-001-modules-vs-standards.md`.

## 4. Three non-aligned score-label scales — **RESOLVED (ADR-005)**

The 10-band letter-grade scale from `docs/15-Scoring-Engine.md`'s GRADE SYSTEM is canonical (it already matches `docs/28-Testing-Strategy.md`'s hard-coded grade-boundary test values). `docs/06-Audit-Engine.md`'s 5 descriptive labels (Poor/Basic/Good/Advanced/Excellent) are kept but remapped onto the grade boundaries. The redundant "AI READINESS LEVELS" section in `docs/15-Scoring-Engine.md` was removed. Both docs now reference one unified Score/Grade/Label table.

See `.project/adr/ADR-005-scoring-grading.md`.

## 5. Inconsistent REST path prefixing in `docs/24-REST-API-Spec.md` — **RESOLVED (ADR-003)**

The Monitoring API and Reporting API tables were corrected to be fully prefixed (`/monitoring/events`, `/monitoring/history`, `/monitoring/reset`, `/reports/history`, `/reports/templates`, `/reports/generate`, `/reports/export`, `/reports/share`, `/reports/cache`). A general 5-point path-naming rule was formalized to prevent recurrence.

See `.project/adr/ADR-003-rest-api-naming.md`.

## 6. `oxy_settings.key` — reserved-word column name — **RESOLVED (ADR-004)**

Renamed to `setting_key`/`setting_value`. A formal database naming-convention section was added to `docs/25-Database-Schema.md` to prevent recurrence in future tables.

See `.project/adr/ADR-004-database-naming.md`.

## 7. Security weighting looks low relative to security's documented importance — OPEN (product decision, not a doc conflict)

`15-Scoring-Engine.md`'s 2% Security category weight was explicitly left unchanged by ADR-005 — it is a product-tuning decision, not a documentation inconsistency, and was out of scope for Phase 0.5. Still tracked in `.project/04-Questions.md` (Q11) for the user to confirm or adjust.
