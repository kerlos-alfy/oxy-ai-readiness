# ADR-001: Relationship Between Modules and AI Standards

**Status:** Accepted
**Date:** 2026-07-24
**Resolves:** `.project/03-Conflicts.md` §3

## Context

`docs/05-Modules.md` defines LLMS, MCP, Agent Skills, and (via Robots/Discovery) robots.txt, ai.txt, humans.txt, auth.md as **Modules** — WordPress-facing feature packages with their own Service/Controller/Repository/audit rules.

`docs/23-AI-Standards-Layer.md` defines the *same* AI specifications as **Standards** — objects implementing `StandardInterface` (`id/name/version/specification/discover/generate/validate/score/monitor/report/supports/migrate`), registered in a separate Standards Registry, living (per `docs/29-Developer-Guide.md`'s project tree) under a top-level `app/Standards/` directory that `docs/04-Folder-Structure.md` never lists.

Left unresolved, this produces either duplicate discover/generate/validate/score/monitor/report logic (one copy owned by the Module, one by the Standard) or an ambiguous folder location for every AI-standard-compliant feature.

## Decision

**A Module is the WordPress integration shell. A Standard is a metadata + lifecycle descriptor owned by a Module, not a parallel implementation.**

1. `StandardInterface` is retained exactly as specified in `docs/23-AI-Standards-Layer.md`, but its lifecycle methods (`discover()`, `generate()`, `validate()`, `score()`, `monitor()`, `report()`) **delegate to the same Generator/Validator/ScoreProvider/Monitor/Reporter classes the owning Module already registers with the core engines** via its `ServiceProvider` (per the `29-Developer-Guide.md` `ExampleModule::register()` pattern). A Standard never re-implements engine logic; it wraps it.
2. The *only* new responsibility a Standard adds beyond what a Module already provides is external-specification metadata that has no Module equivalent: `specification()` (spec URL/version), `version()`/`supports()` (version negotiation), and `migrate()` (spec version migration). This is what powers the Standards Registry, the `/standards/*` REST API, and any future "Standards" admin screen (compatibility matrix, deprecation status).
3. **Not every Module owns a Standard.** Only Modules that implement a published, externally-versioned AI specification register one:

   | Module | Owns Standard(s) |
   |---|---|
   | Robots | `robots.txt` |
   | LLMS | `llms.txt` |
   | Markdown | Markdown Negotiation |
   | Content Signals | Content Signals |
   | MCP | MCP |
   | Agent Skills | Agent Skills |
   | API Catalog | `api-catalog` (`/.well-known/api-catalog`) |
   | Discovery | `ai.txt`, `humans.txt`, `auth.md` (lightweight, no dedicated screen — bundled under the Discovery module as it already owns the "every discovery file" responsibility per 05) |
   | OAuth Discovery | `openid-configuration`, `oauth-authorization-server`, `oauth-protected-resource` |

   Modules with **no** Standard (pure WordPress/platform features, not externally-versioned specs): Dashboard, Audit, Headers, Settings, Logs, License, Updater, Commerce, Analytics, Monitoring, Reports.

4. A Module that owns more than one Standard (Discovery, OAuth Discovery) registers each as a separate `StandardInterface` implementation; it does not merge them into one object.

## Consequences

- No duplicate discovery/generation/validation/scoring/monitoring/reporting logic between a Module and its Standard — single source of truth per capability, satisfying the "single-responsibility" architecture principle in `docs/02-Architecture.md`.
- `app/Standards/` is **removed** as a top-level folder (see ADR-002). A Standard descriptor lives inside its owning Module's folder as `{Module}Standard.php`.
- The Standards Registry (`docs/23-AI-Standards-Layer.md`) remains a real, useful engine — it answers "what AI specs does this site implement, at what version, with what compatibility" — without becoming a second implementation surface.
- `docs/05-Modules.md` and `docs/23-AI-Standards-Layer.md` are both updated to cross-reference this ADR and the ownership table above.
