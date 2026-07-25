# Proposed Phase Plan (DRAFT — awaiting approval)

This plan is Claude's own proposal, derived from the Architecture Dependency Map (`02-Architecture-Dependency-Map.md`), since no doc-sourced phase breakdown exists (see `03-Conflicts.md` #1). It is written so each phase produces a working, tested increment and never depends on a downstream phase's output. **Do not begin Phase 1 until this plan, and the blocking questions in `04-Questions.md`, are resolved with the user.**

| Phase | Name | Scope | Depends on | Exit criteria |
|---|---|---|---|---|
| 0 | Documentation analysis | This deliverable: doc index, requirement map, architecture map, conflicts, questions, risks, phase plan. | — | User approval. |
| 1 | Foundational scaffolding | `composer.json`/`package.json`, plugin bootstrap file, `Core/` (Bootstrap, Container, Kernel, Config, Hooks, Loader), base `Contracts/`, activation/deactivation/uninstall hooks, CI/tooling skeleton (PHPUnit, PHPStan lvl 8, WPCS, ESLint), no feature modules yet. | Resolved Q1–Q2 | Plugin activates cleanly on a clean WP install; container resolves a test service; CI green on empty test suite. |
| 2 | Database & shared infrastructure | Migration runner, Core tables (`oxy_settings`, `oxy_modules`, `oxy_standards`), Repository base classes, Settings Manager, Logger, Cache Service (multi-tier), Queue/Scheduler skeleton, HTTP Client wrapper (timeout/retry/circuit-breaker). | Phase 1 | Migrations run/rollback cleanly; settings round-trip through Repository, not raw `$wpdb`; queue can enqueue+process a no-op job. |
| 3 | Module & Standard SDK skeleton | `ModuleInterface`, `StandardInterface`, Module Registry, Standards Registry, `ServiceProvider` pattern, module lifecycle wiring, one minimal internal "probe" module/standard for validation only (not user-facing). | Phase 2, resolved Q3 | A module can be enabled/disabled at runtime without touching core; events fire on lifecycle transitions. |
| 4 | Discovery Engine | Resource/file/header/endpoint discovery pipeline → Discovery Map, `/discovery/*` REST. | Phase 3 | Discovery Map correctly lists a known fixture resource; read-only, no writes. |
| 5 | Validation Engine | Centralized validator framework, `ValidatorInterface`, `/validation/*` REST. | Phase 4 | A registered validator runs against a Discovery Map entry and returns PASS/WARN/FAIL deterministically. |
| 6 | Generation Engine | Centralized generator framework, `GeneratorInterface`, publish/rollback/cache/version pipeline, `/generation/*` REST. | Phase 5 | A generated resource round-trips through Validation before publish; rollback restores prior version. |
| 7 | Scoring Engine | Weighted scoring, single resolved grade/label scale (Q4), confidence/trend, `/score/*` REST. | Phase 5, resolved Q4 | Score recalculates deterministically from a fixed set of validation results; grade boundary unit tests pass. |
| 8 | First end-to-end module: Robots | Full Robots module (builder, merge/conflict detection, versioning) wired through Discovery→Generation→Validation→Scoring, proving the whole pipeline on one concrete, well-specified feature before parallelizing. | Phases 3–7 | `/robots/*` REST fully functional; snapshot test on generated `robots.txt`; audit rule shows in Scoring output. |
| 9 | Audit Engine orchestration | Rule Engine, Scan Types (Quick/Full/Deep/Developer), `/audit/*` REST, ties Discovery+Validation+Scoring together at the audit level. | Phase 8 | Full/Quick scan executes within documented performance targets on a fixture site and returns a structured report. |
| 10 | Recommendation + Auto Fix Engines | Issue→recommendation pipeline; safe/confirmation/developer fix tiers with backup→execute→verify→rollback. | Phase 9 | AutoFix on a fixture issue is reversible; rollback test suite passes per 28's AutoFix Safety Tests. |
| 11 | Remaining Discovery-pillar modules | LLMS, Headers, Markdown, Content Signals — each repeats the Phase 8 pattern now that the pipeline is proven. | Phase 10, resolved Q3 | Each module has REST + generator + validator + audit rules + snapshot tests, mirroring Robots. |
| 12 | Admin UI shell | React/TS SPA scaffold, design system tokens (03), Dashboard screen wired to REST built so far, Audit screen, module screens for Phase 8/11 modules. | Phase 11 | Dashboard answers "how ready / what's broken / how to fix" using live API data; a11y smoke pass. |
| 13 | Monitoring + Reporting Engines | Change detection, notifications, report generation/export/sharing. | Phase 9 (Audit), Phase 7 (Scoring) | A simulated resource change triggers revalidation + notification; a report exports in at least one format. |
| 14 | AI-native modules | MCP, Agent Skills, API Catalog, OAuth Discovery. | Phase 11 pattern, resolved Q3 | Each has server-card/registry generation, validation, and REST per its spec doc. |
| 15 | Remaining modules + hardening | Commerce, Analytics, License (per Q5), Updater; full CI matrix; multisite pass (per Q6); performance/security audit against 26/27 budgets; packaging/distribution build. | All prior phases | Release-gate checklist (28) fully green; package installs cleanly on a fresh WP site. |

## Working agreement per phase (from CLAUDE.md Mandatory Workflow)
1. Confirm scope against this table before starting.
2. Implement against documented contracts only — no undocumented shortcuts.
3. Run/extend tests for the phase before marking it done.
4. Update `.project/07-Progress-Log.md` and `.project/08-Decision-Log.md`.
5. Re-surface any new conflicts/questions discovered mid-phase rather than resolving them silently.
