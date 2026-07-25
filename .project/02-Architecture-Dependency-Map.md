# Architecture Dependency Map

## 1. Boot-time dependency order (from 02-Architecture + 29-Developer-Guide)

```
oxy-ai-readiness.php (plugin file)
        │
Composer Autoloader
        │
Constants / Config
        │
Application Bootstrap
        │
Service Container  ──────────────► Core Service Providers
        │                                   │
        ▼                                   ▼
   Core Services                    Shared Services
 (Logger, Settings,                (Cache, Filesystem, HTTP Client,
  Config, Hooks,                    Option Manager, Translator,
  Scheduler)                        Validator, Module Loader)
        │
        ▼
  Module Registry  ◄──── depends on ────  Standards Registry
        │                                        │
        ▼                                        ▼
  Modules boot()                        Standards register()/discover()
        │                                        │
        └───────────────┬────────────────────────┘
                         ▼
                  Routes (REST) registered
                         │
                         ▼
                  Events wired
                         │
                         ▼
                  Admin Application (React SPA) mounted
                         │
                         ▼
                    Ready Event
```

Key rule (02, 29): Core may depend on Contracts/DTOs/Repositories/Services/Infrastructure Adapters. Modules may depend only on public Core Contracts, SDK interfaces, shared DTOs, Events, and approved Services — **never** on another module's internal classes, private Core classes, Admin UI internals, concrete DB drivers, or undocumented globals.

## 2. Engine pipeline dependency graph (14–21)

```
                 ┌─────────────────┐
                 │ Discovery Engine │  (finds resources; read-only)
                 └────────┬─────────┘
                          │ Discovery Map
                          ▼
                 ┌─────────────────┐
                 │Validation Engine │  (validates every discovered/generated resource)
                 └────────┬─────────┘
                          │ Rule Results
              ┌───────────┼───────────────┐
              ▼           ▼               ▼
   ┌─────────────────┐ ┌──────────────┐ ┌───────────────────┐
   │  Scoring Engine  │ │Generation Eng│ │   Audit Engine     │
   │ (weighted score, │ │(creates the  │ │ (orchestrates a    │
   │  grade, trend)   │ │ resources    │ │  full rule-based   │
   └────────┬─────────┘ │ Validation   │ │  scan using        │
            │            │ checks)     │ │  Discovery+        │
            │            └──────┬───────┘ │  Validation+       │
            │                   │         │  Scoring)          │
            │                   │         └─────────┬──────────┘
            │                   │                    │
            │                   ▼                    ▼
            │          (Publish → re-validate) ┌─────────────────────┐
            │                                  │Recommendation Engine │
            │                                  └──────────┬───────────┘
            │                                             ▼
            │                                  ┌─────────────────────┐
            │                                  │  Auto Fix Engine     │
            │                                  │ (backup→execute→     │
            │                                  │  verify→rollback,    │
            │                                  │  re-enters Generation│
            │                                  │  + Validation)       │
            │                                  └──────────┬───────────┘
            ▼                                             ▼
   ┌──────────────────────────────────────────────────────────────┐
   │                    Monitoring Engine                          │
   │  (continuously re-triggers Validation on detected change)     │
   └──────────────────────────────┬─────────────────────────────────┘
                                   ▼
                       ┌─────────────────────┐
                       │  Reporting Engine    │  (reads from every engine above)
                       └─────────────────────┘
```

Implication for build order: **Discovery → Validation → Generation/Scoring → Audit orchestration → Recommendation → AutoFix → Monitoring → Reporting** is the only order that avoids building against unstable/undefined upstream contracts. This directly informs the phase plan (see `06-Phase-Plan.md`).

## 3. Module ↔ Engine ↔ Standard relationship

- A **Module** (05) is a WordPress-facing feature package: UI, REST controller, settings, permissions.
- A **Standard** (23) is a spec-facing AI protocol package: discover/generate/validate/score/monitor/report against an external specification (llms.txt, MCP, etc.).
- Per the Plugin SDK (22) and Developer Guide (29) worked examples, a Module registers its Generators/Validators/AuditRules/ScoreProviders/AutoFixes/Monitors/Reporters *into* the central engines via its `ServiceProvider`/`register()` method — it does not own parallel copies of engine logic.
- **Open question**: whether e.g. the LLMS *Module* (05) and the llms.txt *Standard* (23) are the same unit wearing two hats, or two separate objects where the Module is a thin UI/REST wrapper around the Standard's `StandardInterface` implementation. See `03-Conflicts.md` §2.

## 4. Data flow (per 02, single user action)

```
User clicks (Admin SPA)
   → REST Controller (Http/Controllers)
      → Request object (validate/sanitize)
         → Application Service (use case)
            → Domain Logic (engine / business rule)
               → Repository (isolates WordPress/$wpdb access)
                  → WordPress (DB / filesystem / options)
               ← Repository
            ← Domain Logic
         ← Application Service
      ← REST Response (standard envelope)
   ← Admin SPA re-renders (optimistic update / cache)
```

## 5. Cross-cutting infrastructure all engines/modules share

Service Container, Logger, Settings Manager, Cache Service (multi-tier), Queue/Scheduler, Filesystem (safe/atomic writes + checksum), HTTP Client (timeout/retry/circuit-breaker per 27), Event Bus, Validator (shared low-level sanitizers), Translator (i18n). These must exist **before** any engine or module is built (Phase 1–2 in the proposed plan).

## 6. Database dependency order (25)

`oxy_settings`, `oxy_modules`, `oxy_standards` (Core) must exist before `oxy_audits`/`oxy_discovered_resources` (reference audits/modules), which must exist before `oxy_scores`/`oxy_score_history` (reference `audit_id`), which must exist before `oxy_recommendations`/`oxy_autofix_history`/`oxy_snapshots` (reference issues from audits), which must exist before `oxy_monitor_events`/`oxy_health_checks` and `oxy_reports` (aggregate everything above).
