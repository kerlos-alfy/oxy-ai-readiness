# ADR-003: REST API Path Canonicalization

**Status:** Accepted
**Date:** 2026-07-24
**Resolves:** `.project/03-Conflicts.md` §5

## Context

`docs/24-REST-API-Spec.md` is inconsistent about whether listed paths are full paths or paths relative to an implied engine prefix. Most sections show fully-prefixed paths (`/robots/save`, `/llms/generate`), but the "MONITORING API" section lists bare `GET /events` and `GET /history`, and the "REPORTING API" section lists bare `GET /templates`, `POST /generate`, `POST /export`, `POST /share`. Taken literally, these would collide with identically-named routes from other modules (Robots also has `/history`, LLMS also has `/generate`, etc.).

## Decision

**Every REST route, in every doc, is a full path segment under `/wp-json/oxy-ai/v1/`, and every route belonging to a given module/engine MUST include that module/engine's slug as its first path segment. No bare (unprefixed) routes are permitted anywhere in the spec or the implementation.**

Canonical corrected tables (superseding the ambiguous entries in `docs/24-REST-API-Spec.md`):

### Monitoring API
```
GET    /monitoring
GET    /monitoring/events
GET    /monitoring/history
GET    /monitoring/status
POST   /monitoring/start
POST   /monitoring/stop
POST   /monitoring/reset
```
(`/monitoring/reset` added — it existed in `docs/20-Monitoring-Engine.md`'s own REST section but was missing from `24`'s summary table; now consistent.)

### Reporting API
```
GET    /reports
GET    /reports/history
GET    /reports/templates
POST   /reports/generate
POST   /reports/export
POST   /reports/share
DELETE /reports/cache
```

All other engine/module route tables in `docs/24-REST-API-Spec.md` (Discovery, Audit, Scoring, Validation, Generation, AutoFix, Modules, Standards, SDK) were already correctly prefixed and are unchanged.

### General naming rule (formalized, not previously stated explicitly anywhere)

1. Base: `/wp-json/oxy-ai/v1/{module-slug}`.
2. `{module-slug}` is kebab-case and matches the owning Module's `id()` (e.g. `robots`, `llms`, `content-signals`, `api-catalog`, `agent-skills`).
3. Sub-resources are kebab-case nouns; actions are verbs only where no better noun exists (`/run`, `/reset`, `/validate` are acceptable verb-actions per existing convention across every module doc; prefer nouns otherwise).
4. Collection routes: `GET /{module}` (list/overview). Item routes: `GET /{module}/{id}`. Mutating actions: `POST /{module}/{action}`. Deletion: `DELETE /{module}/{id}` or `DELETE /{module}/{resource}`.
5. No route may omit its module-slug segment, without exception.

## Consequences

- `docs/24-REST-API-Spec.md`'s Monitoring API and Reporting API sections are corrected in place.
- Route-collision risk during Phase 10 (Audit Engine orchestration + REST wiring in the phase plan) is eliminated.
- Future engine/module docs must follow the 5-point rule above when they introduce new endpoints.
