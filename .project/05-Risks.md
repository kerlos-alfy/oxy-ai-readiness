# Risks

## R1 — No doc-sourced phase contract (High)
The docs never actually define Phase 1..N (see Conflicts #1). Every phase boundary in `06-Phase-Plan.md` is an inference, not a transcription. If left unapproved, later phases risk drifting from what the user actually intended "Phase 1", "Phase 2", etc. to mean.
**Mitigation**: get explicit sign-off on `06-Phase-Plan.md` before writing any production code; update `.project/06-Phase-Plan.md` as the living source of truth once approved, and re-confirm before each phase per CLAUDE.md workflow step 6.

## R2 — Enterprise-scale spec, unbounded surface area (High)
This is a ~30-module, 9-engine, full-SDK, licensed-multisite-ready commercial WordPress plugin spec. Faithfully implemented, it is a multi-hundred-file, multi-month effort. Session-to-session continuity depends entirely on the `.project/` control files staying accurate and current.
**Mitigation**: strict phase-at-a-time execution (never parallelize unrelated engines), mandatory progress/decision log updates after every phase, avoid speculative scaffolding of modules not yet reached.

## R3 — Specs are vision-level, not fully prescriptive (Medium-High)
Most engine docs describe *what* (responsibilities, categories, REST paths, events, performance targets) but not concrete PHP contracts. Only `29-Developer-Guide.md` gives worked interface examples, and only for a subset of extension points (Generator, Validator, AuditRule, AutoFix, ScoreProvider, Monitor, Reporter, REST route, CLI command, migration, repository). Everything else (e.g., exact `ModuleInterface`/`StandardInterface` method signatures beyond the method *names* listed in 05/23, exact DTO shapes, exact Event payloads) must be designed by Claude, consistent with the patterns shown.
**Mitigation**: when designing an interface not fully specified, follow the closest worked example in 29 for style/conventions, document the decision in the decision log, and flag anything non-obvious as a question rather than silently inventing.

## R4 — Internal doc inconsistencies could propagate into code (Medium)
See `03-Conflicts.md`. If unresolved before the relevant phase, they'll produce inconsistent folder layouts, ambiguous route collisions, or a scoring engine with self-contradictory thresholds.
**Mitigation**: resolve each conflict via `04-Questions.md` before its owning phase starts; do not guess silently on architecturally load-bearing decisions (module structure, Module/Standard relationship).

## R5 — Very strict, currently unimplemented quality bars (Medium)
28-Testing-Strategy.md mandates PHPStan level 8, 95% coverage on security/autofix/scoring, a full CI matrix (PHP 8.1–8.4 × several WP versions × MySQL/MariaDB × single/multisite × plugin combinations), snapshot testing for every generated resource, and release gates that block on any of these. None of this tooling (composer scripts, CI config, PHPUnit/WP test suite bootstrap, PHPStan config, ESLint config) exists yet.
**Mitigation**: stand up the testing/tooling skeleton early (proposed Phase 1) so every subsequent phase can actually satisfy "run tests after every phase" (CLAUDE.md workflow step 7) rather than deferring test infra to the end.

## R6 — WordPress-specific implementation landmines (Low-Medium)
`oxy_settings.key` reserved-word column (Conflict #6); minimizing `wp_options` while still supporting WP's activation/uninstall lifecycle; ensuring generated `robots.txt`/`llms.txt`/headers coexist safely with popular SEO/cache plugins (Rank Math, Yoast, WP Rocket, LiteSpeed, Cloudflare) as required by 07/10/14/28.
**Mitigation**: address these at the point of implementation (migration writing, robots/headers generators) rather than in Phase 0; call out in code review checklist.

## R7 — Frontend-performance and heavy-operation constraints are easy to violate accidentally (Medium)
CLAUDE.md explicitly restricts "Do not run heavy operations during frontend requests," and 27-Performance-Spec.md lists explicit failure conditions (blocking public page rendering, unbounded crawls, endless retries). Naive implementations of Markdown negotiation, Content Signals, or Monitoring could easily trigger heavy work on a public request path if not routed through the queue/cache layers from the start.
**Mitigation**: no module's public-facing request handler may call an engine synchronously without going through cache-first + background-queue patterns established in Phase 1/2 infrastructure.

## R8 — Commercial/licensing scope ambiguity (Low, pending Q5)
Building License/entitlement enforcement prematurely (or, conversely, ignoring it entirely when the user expects a licensing gate) both carry rework cost.
**Mitigation**: resolved by Question 5 before the module is scaffolded; keep License module interface-stubbed but unenforced until scope is confirmed.
