# Release Gate Checklist — v0.1.0 (end of Phase 15)

Per docs/28-Testing-Strategy.md's "RELEASE GATES" section: "A release must not proceed when [any of the following] fail." Each gate below records what was *actually run* to verify it in this session, not an assumed pass. Environment constraints (no live WordPress/MySQL instance, no code-coverage driver, no browser) are stated honestly where they limit what could be verified — these are real, documented gaps, not silently skipped.

| Gate | Status | Evidence |
|---|---|---|
| Critical Tests Fail | **PASS** | `composer test` (Unit): `OK (546 tests, 1118 assertions)`. `composer test:integration`: `OK (3 tests, 1263 assertions)`. |
| Security Tests Fail | **PASS** | `composer audit`: no advisories. `npm audit --omit=dev`: 0 vulnerabilities. Manual review: every one of 23 REST controllers implements `authorize()` via `current_user_can('manage_options')`; no `eval()`/`unserialize()`/`extract()`; no raw `$_GET`/`$_POST`/`$_REQUEST` superglobal access (every REST param flows through `WP_REST_Request`); no direct `$wpdb` usage (Repository pattern throughout); no lingering `var_dump()`/`print_r()`/`error_log()` debug calls; every hook registered (`admin_menu`, `admin_enqueue_scripts`, `script_loader_tag`, `admin_notices`, `rest_api_init`, `plugins_loaded`) is admin-/REST-scoped — none run on public visitor-facing requests. |
| Migration Tests Fail | **N/A** | No `oxy_*` database tables or migration runner exist yet (deferred since Phase 2, still true) — nothing to test. |
| Rollback Tests Fail | **PASS** | `GenerationServiceTest`/`AutoFixServiceTest` rollback scenarios (backup → publish failure → restore) included in the 546 passing Unit tests. |
| Static Analysis Fails | **PASS** | `composer analyse` (PHPStan level 8): `[OK] No errors` across 129 analysed files. |
| Coverage Drops Below Threshold | **DEFERRED (documented)** | No code-coverage driver (Xdebug/PCOV) is installed in this environment — `composer test:coverage` cannot produce a real number here. No numeric threshold has been set anywhere in `.project/` to check against even if it could. Real gap: install a coverage driver and set an explicit threshold before this gate can be enforced for real. |
| Build Fails | **PASS** | `npm run build` (`tsc --noEmit && vite build`) succeeds, produces `dist/.vite/manifest.json` in the shape `AdminServiceProvider` reads. `bash bin/build-release.sh` succeeds, producing a real zip + SHA256 checksum, verified by `tests/Integration/PackagingTest.php`. |
| Package Installation Fails | **PARTIALLY VERIFIED (documented gap)** | Verified: the package's *contents* are correct (real `app/`/`routes/`/`dist/`/`vendor/autoload.php`, no dev-only vendor packages, no dev/test/doc files leaked) — see `PackagingTest`. **Not verified**: actually installing and activating the zip on a live WordPress instance — no WordPress core, MySQL, or web server exists in this sandboxed session to do that against. Real gap: run an actual install/activate/deactivate/uninstall smoke test on a real (or containerized) WordPress site before public distribution. |
| Critical Accessibility Issues Exist | **PARTIALLY VERIFIED (documented gap)** | `DashboardPage.test.tsx` asserts zero `jest-axe` violations — the one screen with an automated a11y check. **Not verified**: Audit/module screens, keyboard-only navigation, or screen-reader behavior — no browser or real user-interaction testing exists in this session. Real gap: a full a11y pass needs `run`/browser tooling this session didn't invoke. |

## Overall gate result

**Conditionally clear to tag `phase-15` and continue development.** Every gate this environment can actually verify passes cleanly. The three "DEFERRED"/"PARTIALLY VERIFIED" rows are genuine, named gaps — not release-blocking failures, but **do not treat this checklist as clearing the plugin for public distribution** until: (1) a real WordPress install/activate/deactivate/uninstall smoke test has been run, (2) a coverage driver + threshold exist and pass, (3) a full browser-based accessibility pass has been run across every screen.

## What changed this phase that this checklist newly covers

- `tests/Integration/PackagingTest.php` — the Integration testsuite went from 0 tests (every prior phase report) to 3 real ones.
- `.github/workflows/ci.yml` — CI now runs automatically on push/PR; before this phase, "CI" only meant "run `composer quality`/`npm run quality` locally by hand."
- `bin/build-release.sh` — first real, working packaging script this project has had.
