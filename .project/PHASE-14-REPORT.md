# Phase 14 Report — AI-native modules (MCP, Agent Skills, API Catalog, OAuth Discovery)

**Date:** 2026-07-26.
**Status:** Complete, validated. Committed, tagged `phase-14`, pushed to GitHub autonomously.

## 1. Scope

Per `06-Phase-Plan.md` row 14: "MCP, Agent Skills, API Catalog, OAuth Discovery," depends on "Phase 11 pattern, resolved Q3," exit criterion "Each has server-card/registry generation, validation, and REST per its spec doc."

## 2. What was implemented

Each module mirrors `Modules/Robots`'s established shape (Module + Standard + ServiceProvider + Controller, wired through Discovery→Validation→Generation, one `GET`/`preview`/`save`/`validate`/`reset` REST set), but every one of the four docs (docs/12, docs/13, docs/05's brief API Catalog/OAuth Discovery sections) describes a much larger aspirational feature set than this phase's exit criterion asks for — the real engineering judgment this phase required was scoping each module's *content* honestly, not just wiring plumbing.

- **MCP** (`app/Modules/Mcp/`): a real Server Card (name/description/organization/version), with every protocol capability (resources/tools/prompts/sampling/streaming) honestly declared `false`/empty — this project has never built a live MCP JSON-RPC transport, so claiming those capabilities would describe infrastructure that doesn't exist.
- **Agent Skills** (`app/Modules/AgentSkills/`): a Skill Registry of exactly three skills, all real — this plugin's own already-working `GET /score`, `POST /audit/start`, `GET /recommendations` REST actions — not docs/13's fabricated example skills (Book Appointment, Find Doctor, Open WhatsApp) that no WordPress site has by default.
- **API Catalog** (`app/Modules/ApiCatalog/`): a real, accurate, hand-maintained 83-route inventory of every route this plugin registers in `routes/api.php`, cross-checked against `ApiRoutesTest`'s own independently-computed route list via a new regression assertion.
- **OAuth Discovery** (`app/Modules/OAuthDiscovery/`): the one module owning three Standards instead of one (per ADR-001's ownership table), so it implements `DiscoveryInterface`/`ValidatorInterface` but not `GeneratorInterface` — three small dedicated Generator classes fill that role instead. `oauth-protected-resource` (RFC 9728) is fully spec-compliant, describing this plugin's own genuinely-protected REST namespace. `openid-configuration`/`oauth-authorization-server` deliberately do **not** fabricate a real OpenID Provider or OAuth Authorization Server — neither exists in this codebase, and their specs require mandatory endpoint fields this project has nothing real to fill in with.

## 3. Real content, not fabricated data — the central engineering judgment this phase required

Every one of the four docs invites exactly the kind of fabrication CLAUDE.md prohibits (full MCP tool execution, example skills like "Find Doctor," spec-compliant OAuth endpoints for a server that doesn't exist). Each module's content was scoped down to what's genuinely real: identity metadata that's true today, this plugin's own actually-callable REST actions, an accurate route inventory, and an honest "not configured" declaration where a real capability doesn't exist yet. This is logged in detail in `DECISIONS.md` — five separate, substantial decisions, one per module plus the OAuth Discovery generator-splitting design.

## 4. A real end-to-end gap caught and fixed

`composer test` failed once, for real, before this phase was done: the existing full-system `RobotsScoringEndToEndTest` (Phase 8) boots the actual `Plugin` and runs every registered validator against every discovered resource — confirming, not merely assuming, that `ValidationService::validate($resource)` runs system-wide across every module. Adding `OAuthDiscoveryModule`'s validator (which calls its Generators' real `home_url()`/`rest_url()`) was the first validator in this codebase to call a WordPress runtime function reachable from that path, and that one test hadn't stubbed either function. Fixed by adding both stubs to that one test, with a comment explaining why. Also fixed 4 new controller tests missing a `resourceId()` mock expectation (needed by `GenerationService::publish()`, an easy miss when a mocked `GeneratorInterface` doesn't stub every method a caller happens to use) and one `OAuthDiscoveryControllerTest` case needing all three real Generators registered before calling `index()`.

## 5. Checks performed — all run for real

`composer validate` → valid. `composer test` → `OK (475 tests, 958 assertions)` — up from 369/701 at the end of Phase 13. PHPStan level 8 → `[OK] No errors` across 117 analysed files (up from 95). PHPCS (hybrid ruleset) → 0 errors, 0 warnings across 210 files. `composer quality` → all green. `npm run quality` re-verified green (frontend untouched this phase).

## 6. Documentation updates

None to `docs/*`.

## 7. Decisions requiring your attention

Full detail in `.project/DECISIONS.md`. Most likely to need a second look:

- **MCP's capabilities are all honestly false/empty** — no live MCP transport exists; flip these only as real capabilities are actually built.
- **Agent Skills publishes this plugin's own REST actions, not docs/13's example skills** — a deliberate substitution of real capability for fabricated example content.
- **API Catalog is a hand-maintained static list, not live route introspection** — accurate today, needs manual updates as routes change.
- **OAuth Discovery's `openid-configuration`/`oauth-authorization-server` do not claim a real OAuth server exists** — the single most consequential scoping decision this phase made; a wrong call here would mean publishing misleading security-relevant discovery metadata.

## 8. Files created/modified this phase

New: 17 Module/Standard/Generator/ServiceProvider files across `app/Modules/{Mcp,AgentSkills,ApiCatalog,OAuthDiscovery}/` + 5 `app/Http/Controllers/*` files + 22 new test files. Modified: `app/Core/Plugin.php` (registers all 4 new providers), `routes/api.php` (44 new routes), `tests/Unit/EndToEnd/RobotsScoringEndToEndTest.php` (WP function stubs), `tests/Unit/Routes/ApiRoutesTest.php` (extended + new regression assertion). `.project/PROGRESS.md`, `.project/DECISIONS.md`, `.project/FILE-MANIFEST.md` updated, this report new.

## 9. What's explicitly still missing (by design — later phases)

A real MCP JSON-RPC transport (resource/tool/prompt execution, sampling, streaming); a real OpenID Provider / OAuth 2.0 Authorization Server; per-skill CRUD REST routes (`/skills/{id}`, `/skills/test`, `/skills/import`, `/skills/export`); live `rest_get_server()` route introspection for API Catalog; `Core/Scheduler.php`; any custom `oxy_*` database table or migration; Settings Manager, Logger service, Cache Service, Queue; Commerce/Analytics/License/Updater modules; CI matrix; multisite validation; security/performance hardening; packaging and distribution build (all Phase 15).

## 10. Git

Committed as "Phase 14: AI-native modules (MCP, Agent Skills, API Catalog, OAuth Discovery)," tagged `phase-14`, pushed to `origin/main` along with the tag.

---

**Phase 14 complete. Proceeding directly to Phase 15 per the user's explicit combined-cycle instruction.**
