# ADR-002: Canonical Folder Structure

**Status:** Accepted
**Date:** 2026-07-24
**Resolves:** `.project/03-Conflicts.md` §2 (and the `app/Standards/` question raised in §3 / ADR-001)

## Context

Three incompatible module folder templates existed:

- `docs/04-Folder-Structure.md`: flat `Module.php/Service.php/Controller.php/.../Assets/Views/Tests/Config/` (PascalCase files, no `ServiceProvider`, server-rendered-style `Views/`+`Assets/`).
- `docs/22-Plugin-SDK.md`: `Module.php/ServiceProvider.php/config/generators/validators/monitors/scoring/reports/autofix/discovery/migrations/routes/resources/assets/translations/tests/` (lowercase plural subfolders).
- `docs/29-Developer-Guide.md`: `{Module}Module.php/{Module}ServiceProvider.php/config/Discovery/Generators/Validators/Scoring/AutoFix/Monitoring/Reports/Http/{Controllers,Requests}/Routes/Resources/Database/Migrations/Tests/` (PascalCase subfolders, lowercase `config/`, worked code examples).

Additionally, `docs/03-UI.md` and `docs/29-Developer-Guide.md`'s "ADDING AN ADMIN SCREEN" section establish the admin UI as a single centralized React/TypeScript SPA (`assets/src/admin/screens/{Screen}/`), not per-module server-rendered views — making `04`'s per-module `Views/`+`Assets/` folders obsolete.

## Decision

**`docs/29-Developer-Guide.md`'s structure is canonical**, because it is the only one of the three backed by working code examples that the rest of that same document (and `docs/28-Testing-Strategy.md`'s namespace-based test organization) already depends on. It is adopted with two additions (a `Standard.php` file per ADR-001, and `Repositories/` promoted to a first-class subfolder since `docs/02-Architecture.md` mandates the Repository pattern for every module) and one removal (`Views/`/`Assets/`, superseded by the centralized SPA).

### Canonical module folder

```
app/Modules/{ModuleName}/
├── {ModuleName}Module.php            # ModuleInterface implementation
├── {ModuleName}ServiceProvider.php   # DI bindings (register/boot)
├── {ModuleName}Standard.php          # StandardInterface impl — ONLY if this module owns a Standard (ADR-001)
├── config/
│   └── {module-slug}.php
├── Discovery/
├── Generators/
├── Validators/
├── Scoring/
├── AutoFix/
├── Monitoring/
├── Reports/
├── Repositories/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Routes/
│   └── api.php
├── Database/
│   └── Migrations/
├── Resources/
├── Events/
├── Tests/
│   ├── Unit/
│   └── Integration/
└── README.md                          # per the template in 29-Developer-Guide.md
```

### Canonical root structure

Unchanged from `docs/04-Folder-Structure.md` **except**: `app/Standards/` is removed as a top-level directory (folded into modules per ADR-001). `app/` retains: `Admin/, Core/, Domain/, Application/, Infrastructure/, Modules/, Services/, Repositories/, Support/, Traits/, Contracts/, DTO/, Events/, Exceptions/, Helpers/, Http/, Console/, Jobs/, Models/, Policies/, Providers/, Validators/`.

Two additions to `Core/` (flat files, matching its existing style — `Application.php, Bootstrap.php, Plugin.php, Loader.php, Container.php, Kernel.php, Config.php, Hooks.php, Scheduler.php`):
- `Core/ModuleRegistry.php`
- `Core/StandardsRegistry.php`

One addition to `Contracts/`:
- `Contracts/StandardInterface.php` (alongside the existing `ModuleInterface.php`)

### Admin UI

Confirmed centralized, not per-module: `assets/react/{Dashboard,Audit,Robots,Markdown,Headers,Settings,Components,Layouts,Hooks,Contexts,Store,Utils}/` per `docs/04-Folder-Structure.md`'s existing React section, consistent with `docs/29-Developer-Guide.md`'s `assets/src/admin/screens/{Screen}/` pattern. (Doc uses two slightly different asset-root names, `assets/react/` vs `assets/src/admin/`; both docs are updated to standardize on `assets/react/` to match `04`'s already-detailed React subfolder breakdown.)

## Consequences

- `docs/04-Folder-Structure.md` and `docs/22-Plugin-SDK.md` are updated to reference this canonical structure instead of their own now-superseded variants.
- `docs/29-Developer-Guide.md` is updated only to remove its standalone `app/Standards/ExampleStandard/` section (folded into the module example, per ADR-001) and its `assets/src/admin/` path is aligned to `assets/react/`.
- Every future module scaffold (Phase 3 onward) follows this template exactly; no per-module deviation without a new ADR.
