# Documentation Index

Phase 0 record of every source document read before any implementation work, per `docs/30-Claude-Code-Master.md` and `CLAUDE.md`.

| # | File | Role | One-line summary |
|---|------|------|-------------------|
| — | `CLAUDE.md` | Root instructions | Mandatory workflow + restrictions for Claude Code on this repo. |
| 01 | `docs/01-Vision.md` | Product vision | Premium WP plugin optimizing sites for AI search/agents/LLMs; 4 pillars (Discovery, Content, Infrastructure, Audit); not an SEO/security/cache plugin. |
| 02 | `docs/02-Architecture.md` | Software architecture | Layered (Presentation/Application/Domain/Infrastructure), Service Container + DI, Module System, Repository pattern, Event system, no direct module-to-module calls. |
| 03 | `docs/03-UI.md` | UI/UX spec | SaaS-grade admin UI (Elementor/Rank Math/Linear inspired), color palette, layout, dashboard/screen-by-screen wireframe descriptions, accessibility (WCAG AA). |
| 04 | `docs/04-Folder-Structure.md` | Folder structure | Root tree (`app/`, `assets/`, `config/`, `database/`, `routes/`, `storage/`, `templates/`, `tests/`), module folder template, 500-line file limit. |
| 05 | `docs/05-Modules.md` | Module specifications | ModuleInterface contract; per-module purpose/responsibilities/REST/audit rules for ~20 modules (Dashboard, Audit, Robots, LLMS, Markdown, Headers, Content Signals, Discovery, API Catalog, MCP, OAuth, Agent Skills, Commerce, Analytics, Monitoring, Reports, Settings, Logs, License, Updater) + future modules list. |
| 06 | `docs/06-Audit-Engine.md` | Audit Engine | Rule-based scanner (PASS/WARN/FAIL/INFO/SKIPPED), scan types (Quick/Full/Deep/Developer), category checklists, score bands, recommendation/autofix/reporting tie-in, `/audit/*` REST. |
| 07 | `docs/07-Robots-Spec.md` | Robots module | Visual robots.txt builder, supported crawlers list (incl. AI bots), 5 modes, merge/conflict detection with SEO plugins, versioning/backup, `/robots/*` REST. |
| 08 | `docs/08-LLMS-Spec.md` | LLMS module | Generates/maintains `llms.txt` as "AI homepage", smart content discovery + prioritization, multi-language, templates, `/llms/*` REST. |
| 09 | `docs/09-Markdown-Spec.md` | Markdown module | HTML→Markdown content negotiation per post/page/product, builder support (Elementor/Gutenberg/WooCommerce), caching, chunking, `/markdown/*` REST. |
| 10 | `docs/10-Headers-Spec.md` | Headers module | Centralized HTTP header manager (AI discovery, negotiation, cache, security, SEO categories), Link header relations, conflict detection, `/headers/*` REST. |
| 11 | `docs/11-Content-Signals-Spec.md` | Content Signals module | Machine-readable semantic metadata (identity/purpose/audience/trust/freshness/knowledge/AI-usage/compliance signals), `/content-signals/*` REST. |
| 12 | `docs/12-MCP-Spec.md` | MCP module | Model Context Protocol server card, resources/tools/prompts, auth modes, health checks, `/mcp/*` REST. |
| 13 | `docs/13-Agent-Skills-Spec.md` | Agent Skills module | Publishes structured "capabilities" (not just content) AI agents can execute; skill registry/builder/tester, `/skills/*` REST. |
| 14 | `docs/14-Discovery-Engine.md` | Discovery Engine | Central resource/capability/endpoint/file/header/schema/plugin/server discovery pipeline feeding a "Discovery Map"; `/discovery/*` REST. |
| 15 | `docs/15-Scoring-Engine.md` | Scoring Engine | Weighted category scoring pipeline, grade system (A+–F), separate "AI Readiness Levels" labels, confidence/trend/benchmark/bonus/penalty systems, `/score/*` REST. |
| 16 | `docs/16-Validation-Engine.md` | Validation Engine | Centralized validation framework for every generated/discovered resource; PASS/WARNING/FAIL/INFO/SKIPPED/UNKNOWN; `/validation/*` REST. |
| 17 | `docs/17-Generation-Engine.md` | Generation Engine | Centralized generator framework (GeneratorInterface: supports/generate/validate/preview/publish/rollback/cache/version); `/generation/*` REST. |
| 18 | `docs/18-AutoFix-Engine.md` | Auto Fix Engine | Safe/confirmation/developer/experimental fix levels, backup→execute→verify→rollback pipeline, `/autofix/*` REST. |
| 19 | `docs/19-Recommendation-Engine.md` | Recommendation Engine | Turns audit issues into prioritized, grouped, explained recommendations + executive summaries; `/recommendations/*` REST. |
| 20 | `docs/20-Monitoring-Engine.md` | Monitoring Engine | Continuous change/health detection across files/headers/WP/server, notification delivery, `/monitoring/*` REST. |
| 21 | `docs/21-Reporting-Engine.md` | Reporting Engine | Executive/Technical/Agency/White-Label/Change/Historical report types, export formats, sharing, scheduling. |
| 22 | `docs/22-Plugin-SDK.md` | Plugin SDK | Extension framework: OxyModule base class, ServiceProvider, module lifecycle, hook/event bus, module manifest (`module.json`), marketplace-readiness, SDK interfaces list. |
| 23 | `docs/23-AI-Standards-Layer.md` | AI Standards Layer | Separate abstraction (Standards Registry) for AI web standards (llms.txt, MCP, Agent Skills, auth.md, ai.txt, humans.txt, etc.) via `StandardInterface`, independent lifecycle from Modules. |
| 24 | `docs/24-REST-API-Spec.md` | REST API spec | `/wp-json/oxy-ai/v1` base, versioning, auth methods, per-engine endpoint list, rate limiting, OpenAPI 3.1, webhooks, deprecation policy. |
| 25 | `docs/25-Database-Schema.md` | Database schema | Dedicated tables (not options-table-heavy) grouped by domain: core/audit/discovery/validation/scoring/monitoring/autofix/reports/recommendations/license/cache/queue/analytics; retention policy; multisite; cloud-sync fields (UUID/checksum). |
| 26 | `docs/26-Security-Spec.md` | Security spec | Zero-trust principles, auth/authz model, default capabilities + role matrix, input/output handling, CSRF/nonce, encryption, audit logging, incident response, OWASP/PSR/GDPR compliance targets. |
| 27 | `docs/27-Performance-Spec.md` | Performance spec | Hard perf targets (dashboard <1.5s, API reads <300ms, etc.), caching architecture, queue/job system, incremental auditing, memory/HTTP/crawler limits, degradation strategy, observability. |
| 28 | `docs/28-Testing-Strategy.md` | Testing strategy | Full test pyramid, environment matrix (PHP 8.1–8.4 × WP versions × MySQL/MariaDB), coverage minimums (80% overall, 95% security/autofix/scoring), CI pipeline, release gates, chaos/fuzz/security testing. |
| 29 | `docs/29-Developer-Guide.md` | Developer guide | Concrete setup/workflow reference: repo/env setup, project structure, worked code examples for ServiceProvider/Module/Standard/Generator/Validator/AuditRule/AutoFix/ScoreProvider/Monitor/Reporter/REST route/CLI/migration/repository, coding standards, git/PR/release workflow. |
| 30 | `docs/30-Claude-Code-Master.md` | "Master Execution Contract" | **Byte-identical to `CLAUDE.md`.** Contains only the same Mandatory Workflow / Restrictions / Phase 0 instructions — no distinct phase breakdown or file list beyond what's in `CLAUDE.md`. See `03-Conflicts.md`. |

Total: 30 spec documents + root instructions file, all read in full this session.
