# Requirement Map

Cross-cutting requirements extracted from all 30 docs, organized by system area. This is the basis for the Implementation Map and Phase Plan.

## 1. Core Platform (Architecture, Folder Structure, Plugin SDK, Developer Guide)

- PHP 8.1+ (recommend 8.3+), strict_types, WordPress latest stable (+ 2 prior majors), MySQL 5.7+/MariaDB 10.6+, Node 20 LTS, Composer 2.7+.
- Namespace root: `OxyAI\` (per 29). PSR-4 autoloading via Composer.
- Bootstrap sequence: Composer autoload → Constants → Service Container → Register Services → Core → Modules → Hooks → REST → Admin → Ready.
- Layers: Presentation → Application → Domain → Infrastructure, each communicating only with adjacent layers.
- DI required everywhere in business logic; `new Service()` inside services forbidden; singletons forbidden except Bootstrap/Container/Config.
- Repository pattern mandatory: services never query WordPress (`$wpdb`, `WP_Query`) directly.
- Module system: every feature is an isolated `ModuleInterface` implementation (register/boot/init/assets/routes/settings/permissions/audit/shutdown) with its own Controller/Service/Repository/Routes/Views/Assets/Tests/Config; no module may reach into another module's internals — only via Events, Interfaces, or the Service Container.
- Event system decouples modules (Before/After Audit, Before/After Save, RobotsGenerated, HeadersUpdated, ModuleEnabled/Disabled, etc.).
- Max ~500 lines per file; split when exceeded.
- Every module must be independently removable without breaking others.

## 2. AI Standards Layer (23)

- Parallel abstraction to Modules: `StandardInterface` (id/name/version/specification/discover/generate/validate/score/monitor/report/supports/migrate) managed by a Standards Registry.
- Standards: robots.txt, llms.txt, auth.md, ai.txt, humans.txt, api-catalog, Markdown Negotiation, Content Signals, MCP, Agent Skills, OpenAPI, JSON Feed, RSS, Sitemap.
- Standard lifecycle: Install→Register→Discover→Generate→Validate→Score→Monitor→Report→Update→Deprecate→Remove.
- **Relationship to Modules is unresolved** — see Conflicts doc.

## 3. Core Engines (pipeline, each centralized — not reimplemented per module)

| Engine | Doc | Core responsibility | Feeds into |
|---|---|---|---|
| Discovery Engine | 14 | Finds every AI resource/capability exposed by the site → Discovery Map | Validation, Audit, Monitoring |
| Validation Engine | 16 | One validation framework for every generated/discovered resource | Scoring, Generation, AutoFix |
| Generation Engine | 17 | One generator framework for every AI resource/file/header | Validation (post-generation), Publishing |
| Scoring Engine | 15 | Weighted category scoring → grade + confidence + trend | Dashboard, Reports, Recommendations |
| Audit Engine | 06 | Orchestrates Discovery+Validation+Scoring into rule-based scans (Quick/Full/Deep/Developer) | Recommendation, AutoFix, Reports |
| Recommendation Engine | 19 | Turns audit issues into prioritized/grouped/explained action plans | Dashboard, AutoFix |
| Auto Fix Engine | 18 | Backup→execute→verify→rollback safe remediation | Score update, Reports |
| Monitoring Engine | 20 | Continuous change/health detection, notifications | Validation (re-trigger), Reports |
| Reporting Engine | 21 | Aggregates all engine output into Executive/Technical/Agency/etc. reports | External export/sharing |

Cross-engine rule: **every capability class of every engine must be pluggable via interfaces registered through the Module/Standard SDK — the core engines themselves must never require modification to add a new module, standard, generator, validator, rule, score provider, monitor, or reporter.**

## 4. Feature Modules (05, 07–13)

Each module below must ship: Service, Controller, Repository, REST routes, audit rules, settings UI, and (where applicable) a Generator + Validator registered with the central engines.

- Dashboard — score/quick-actions/activity widgets, `/dashboard*`
- Audit — orchestration UI/API, `/audit/*`
- Robots — visual robots.txt builder, `/robots/*`
- LLMS — llms.txt generator/editor, `/llms/*`
- Markdown — HTML→MD negotiation engine, `/markdown/*`
- Headers — HTTP header manager, `/headers/*`
- Content Signals — semantic metadata builder, `/content-signals/*`
- Discovery — discovery file management UI (llms.txt/auth.md/ai.txt/humans.txt/robots.txt/well-known)
- API Catalog — `/.well-known/api-catalog` generator, `/api-catalog`
- MCP — MCP server card/resources/tools/prompts, `/mcp/*`
- OAuth Discovery — openid-configuration / oauth-authorization-server / oauth-protected-resource
- Agent Skills — skill registry/builder/tester, `/skills/*`
- Commerce (future-facing) — x402/agent payments, standards-only for now
- Analytics — AI crawler/usage tracking + charts
- Monitoring, Reports, Settings, Logs, License, Updater — platform-support modules

## 5. REST API (24)

- Base: `/wp-json/oxy-ai/v1`, versioned (v1/v2/v3 + experimental/deprecated/legacy).
- Every dashboard feature must have a corresponding API endpoint — **no dashboard-only functionality** (explicit CLAUDE.md restriction, reinforced by 24's Success Criteria).
- Auth: WP auth, Application Passwords, OAuth 2.1, JWT, API keys, Bearer tokens.
- Standard response envelope (success/status/message/data/meta/pagination/execution_time/version/request_id) and error envelope (status/error_code/message/details/doc_url/request_id/timestamp).
- Rate limiting (60/hr anonymous, 1000/hr authenticated), pagination, filtering, sorting conventions.
- OpenAPI 3.1 documentation required; 24-month minimum deprecation support.

## 6. Database (25)

- Minimize `wp_options` writes; use dedicated `oxy_*` tables for operational/historical data.
- Table groups: Core (settings/modules/standards), Audit, Discovery, Validation, Scoring, Monitoring, AutoFix (+snapshots), Reports, Recommendations, License, Cache, Queue (jobs), Analytics.
- All migrations versioned/reversible/atomic/incremental; `dbDelta` pattern per 29's example.
- Retention: audit history 365d default, monitoring events 180d, logs 90d, reports configurable.
- Multisite: per-site isolation + shared license/standards.
- Cloud-sync readiness: every record should support UUID/version/checksum/updated_at/sync_status.

## 7. Security (26)

- Zero Trust, Least Privilege, Defense in Depth, Secure by Default, Fail Secure, Audit Everything.
- Capability-based authz (`manage_oxy`, `view_audit`, `run_audit`, `manage_generation`, `manage_autofix`, `view_reports`, `manage_modules`, `manage_standards`, `manage_monitoring`, `manage_license`, `developer_mode`).
- Every write op: authentication + capability + nonce + input schema + resource ownership + feature/license entitlement.
- Every file op: canonical path + allowed-dir + filename + MIME + extension + permission + checksum validation.
- Prepared statements only; encryption for secrets; immutable audit log with timestamp/user/IP/action/resource/result/reason/request_id.
- Security headers (CSP, X-Frame-Options, HSTS, etc.) as a first-class deliverable of the Headers module.

## 8. Performance (27)

- Hard budgets: dashboard load <1.5s, API read <300ms/write <500ms, quick audit <2s, score calc <100ms, DB query <100ms, admin JS <300KB / CSS <100KB compressed.
- No heavy ops on frontend requests (explicit CLAUDE.md restriction) — no audit/monitoring/generation blocking visitor page loads.
- Background job/queue system required (priority, retries, backoff, dead-letter, cancellation, progress) — supports WP-Cron/Action Scheduler/real cron/CLI/cloud workers.
- Incremental auditing (affected-resource-only revalidation, not full-site rescans) via checksums/hashes/timestamps/dependency graphs.
- Multi-tier cache architecture (request/object/transient/filesystem/Redis/CDN) with tagged invalidation.
- Explicit failure conditions the plugin must never trigger: memory exhaustion, DB locks, blocked frontend, unbounded crawls/logs/retries.

## 9. Testing (28)

- Full pyramid: Unit → Integration/Component/API → E2E, plus Contract/CLI/DB/Security/Performance/Compatibility/Regression/Snapshot/Migration/Release testing.
- Coverage minimums: 80% overall, 90% core engines & REST controllers, 95% security/autofix/scoring, 70% UI.
- PHPStan level 8 minimum; WPCS + PSR-12-where-compatible; ESLint/TypeScript strict.
- CI matrix: PHP 8.1–8.4 × WP versions × MySQL/MariaDB × single/multisite × WooCommerce/Elementor/object-cache on/off.
- Release gates block on: critical/security/migration/rollback test failure, static analysis failure, coverage regression, build/package failure, critical a11y issues.
- Every bug fix requires a regression test; every generated resource requires a snapshot test; every AutoFix requires a rollback test.

## 10. UI/UX (03)

- Design system: Inter font, defined color palette (#2563EB primary, semantic success/warning/danger), 16px card radius, soft shadows only, 200ms ease-in-out transitions, Lucide icons.
- Sidebar (280px) + header (72px) SPA-style admin, React + TypeScript + WordPress Components/Data/api-fetch stack (per 29).
- Dashboard must answer 3 questions immediately: How ready? What's broken? How to fix it?
- WCAG AA, keyboard nav, dark mode, responsive (desktop/tablet/mobile with collapsible sidebar).
- Every error must show: Explanation, Impact, Recommendation, Automatic Fix (if available), Documentation link.

## 11. Governance / Process (CLAUDE.md, 29)

- Conventional commits, branch naming (`feature/`, `fix/`, `refactor/`, `docs/`, `test/`, `security/`).
- PR must include summary/reason/notes/tests/screenshots/migration notes/security & performance impact/backward-compat/doc updates.
- Semantic Versioning; deprecation process (mark → document replacement → warn → maintain compat → migration guide → remove at major).
- Definition of Done: implementation + tests (unit/integration/permission/failure) + docs + static analysis + perf/security review + green CI.
