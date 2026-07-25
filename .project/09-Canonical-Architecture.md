# Canonical Architecture (post Phase 0.5)

Single consolidated reference reflecting every decision made in Architecture Normalization. Supersedes any conflicting statement elsewhere in `.project/01-Requirement-Map.md` or `.project/02-Architecture-Dependency-Map.md` (those files describe the pre-normalization state and are kept for history; this file is the current source of truth for architecture-level decisions). The underlying `docs/*.md` files themselves have also been updated in place — this file exists for at-a-glance reference across sessions.

## 1. Modules vs Standards (ADR-001)

- **Module** = WordPress integration shell: UI, REST, settings, permissions, lifecycle (`ModuleInterface`).
- **Standard** = metadata/lifecycle descriptor for an externally-versioned AI spec, owned by exactly one Module, implementing `StandardInterface`. Delegates `discover/generate/validate/score/monitor/report` to the owning Module's already-registered Generator/Validator/ScoreProvider/Monitor/Reporter. Adds only `specification()/version()/supports()/migrate()`.
- Ownership table:

  | Module | Standard(s) |
  |---|---|
  | Robots | robots.txt |
  | LLMS | llms.txt |
  | Markdown | Markdown Negotiation |
  | Content Signals | Content Signals |
  | MCP | MCP |
  | Agent Skills | Agent Skills |
  | API Catalog | api-catalog |
  | Discovery | ai.txt, humans.txt, auth.md |
  | OAuth Discovery | openid-configuration, oauth-authorization-server, oauth-protected-resource |

  No Standard: Dashboard, Audit, Headers, Settings, Logs, License, Updater, Commerce, Analytics, Monitoring, Reports.

## 2. Folder structure (ADR-002)

```
app/
├── Admin/, Core/ (+ ModuleRegistry.php, StandardsRegistry.php), Domain/, Application/, Infrastructure/,
├── Services/, Repositories/, Support/, Traits/,
├── Contracts/ (+ StandardInterface.php), DTO/, Events/, Exceptions/, Helpers/,
├── Http/, Console/, Jobs/, Models/, Policies/, Providers/, Validators/
└── Modules/
    └── {ModuleName}/
        ├── {ModuleName}Module.php
        ├── {ModuleName}ServiceProvider.php
        ├── {ModuleName}Standard.php        # only if this module owns a Standard
        ├── config/
        ├── Discovery/  Generators/  Validators/  Scoring/  AutoFix/  Monitoring/  Reports/
        ├── Repositories/
        ├── Http/{Controllers/, Requests/}
        ├── Routes/
        ├── Database/Migrations/
        ├── Resources/
        ├── Events/
        ├── Tests/{Unit/, Integration/}
        └── README.md
```

No `app/Standards/` top-level directory. No per-module `Views/`/`Assets/` — admin UI is the centralized `assets/react/` SPA.

## 3. REST API (ADR-003)

- Base: `/wp-json/oxy-ai/v1/{module-slug}` — kebab-case slug, always present, never omitted.
- Corrected: Monitoring (`/monitoring/events`, `/monitoring/history`, `/monitoring/status`, `/monitoring/start`, `/monitoring/stop`, `/monitoring/reset`), Reporting (`/reports/history`, `/reports/templates`, `/reports/generate`, `/reports/export`, `/reports/share`, `/reports/cache`).
- All other engine/module route tables were already correctly prefixed.

## 4. Database naming (ADR-004)

- `oxy_settings` columns: `id, setting_key (UNIQUE), setting_value, type, autoload, created_at, updated_at`.
- Convention: `{$wpdb->prefix}oxy_{domain}` tables, `id` surrogate PK everywhere, `{table_singular}_id` FKs, `created_at`/`updated_at` DATETIME UTC, no reserved-word bare column names, optional cloud-sync fields per-table.

## 5. Score / Grade / Label (ADR-005)

Single canonical table (referenced by both `docs/06-Audit-Engine.md` and `docs/15-Scoring-Engine.md`):

| Score | Grade | Label |
|---|---|---|
| 98–100 | A+ | Excellent |
| 95–97 | A | Excellent |
| 90–94 | A- | Excellent |
| 85–89 | B+ | Advanced |
| 80–84 | B | Advanced |
| 75–79 | B- | Advanced |
| 70–74 | C+ | Good |
| 60–69 | C | Good |
| 40–59 | D | Basic |
| 0–39 | F | Poor |

Category weights (Discovery 20%, Content 20%, Infrastructure 15%, Headers 10%, Markdown 10%, LLMS 10%, MCP 5%, Agent Skills 5%, Performance 3%, Security 2%) are unchanged — explicitly out of scope, flagged as Question 11.

## Files touched in Phase 0.5

- Created: `.project/adr/ADR-001-modules-vs-standards.md`, `ADR-002-folder-structure.md`, `ADR-003-rest-api-naming.md`, `ADR-004-database-naming.md`, `ADR-005-scoring-grading.md`, `.project/09-Canonical-Architecture.md` (this file).
- Updated: `docs/05-Modules.md`, `docs/23-AI-Standards-Layer.md`, `docs/04-Folder-Structure.md`, `docs/22-Plugin-SDK.md`, `docs/29-Developer-Guide.md`, `docs/24-REST-API-Spec.md`, `docs/25-Database-Schema.md`, `docs/06-Audit-Engine.md`, `docs/15-Scoring-Engine.md`.
- Updated: `.project/03-Conflicts.md`, `.project/04-Questions.md`, `.project/07-Progress-Log.md`, `.project/08-Decision-Log.md`.
- No production code, no `app/`, `assets/`, `composer.json`, or plugin bootstrap files created — scope restriction honored.
