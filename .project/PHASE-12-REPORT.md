# Phase 12 Report — Admin UI shell

**Date:** 2026-07-25.
**Status:** Complete, validated. Committed, tagged `phase-12`, pushed to GitHub autonomously.

## 1. Scope

Per `06-Phase-Plan.md` row 12: "React/TS SPA scaffold, design system tokens (03), Dashboard screen wired to REST built so far, Audit screen, module screens for Phase 8/11 modules," exit criterion "Dashboard answers 'how ready / what's broken / how to fix' using live API data; a11y smoke pass."

## 2. Starting state: real, unfinished work already in the repo

This session resumed rather than started the phase. `git status` showed a full React/TS SPA already present (`package.json`, Vite/Tailwind/Jest/ESLint config, all of `assets/react/**`) but entirely untracked, with no `.project` record and no `PHASE-12-REPORT.md`. Before writing anything, I read every one of those files to confirm it was real, working code rather than a stub:

- `DashboardPage.tsx`/`AuditPage.tsx`/`ModulePage.tsx` genuinely call `/score`, `/audit`, `/recommendations`, and the five modules' `/{slug}` REST surfaces — no mock data baked in.
- `DashboardPage.test.tsx` mocks real REST payloads and asserts both content (`92%`, broken results, recommendations) and a `jest-axe` a11y pass.
- `Utils/api.ts` and `main.tsx` explicitly named `app/Admin/AdminServiceProvider` (in comments) as the PHP piece that was supposed to localize `window.oxyAiReadiness` and mount `#oxy-ai-readiness-root` — and that file didn't exist yet.

So the actual gap was precise: the entire PHP-side admin wiring, plus verifying the frontend tooling actually installs and runs (it had never been through `npm install`).

## 3. What was built

- **`app/Admin/AdminServiceProvider.php`** — the missing piece. Registers one top-level wp-admin menu page (`manage_options`), renders only an empty `#oxy-ai-readiness-root` mount node (the SPA owns all in-page navigation itself), reads Vite's build manifest (`dist/.vite/manifest.json`) through an injectable `FileRepository` so the enqueued hashed filename is never hardcoded, falls back to a real `admin_notices` error (not a fatal) if `dist/` hasn't been built, marks its own script tag `type="module"` via `script_loader_tag`, and localizes `window.oxyAiReadiness` (`restUrl`/`nonce`/`version`) exactly as the already-written frontend expected. Wired into `Plugin.php`'s provider list.
- **`tests/Unit/Admin/AdminServiceProviderTest.php`** — 9 tests covering hook registration, the exact `add_menu_page()` call, the enqueue callback's page-suffix guard, the missing-build notice path, the full enqueue+localize path (manifest served through an injected `FileRepository`/`InMemoryFilesystem`, no WordPress globals touched), both `printf`-based render methods' output (via `expectOutputString`, since `beStrictAboutOutputDuringTests` is set project-wide), and the `script_loader_tag` filter's handle-scoping.
- **Two real bugs fixed in the untested frontend scaffold**, not worked around: `eslint-plugin-react-hooks@^4.6.2`'s peer-dependency range didn't support the already-pinned `eslint@^9.9.0`, so `npm install` failed outright with `ERESOLVE` — bumped to `^5.0.0`. `eslint.config.js` had no real global-environment configuration (only 4 manual globals), so linting failed with 25 `no-undef` false positives on ambient DOM/JSX/Jest types across nearly every file — disabled `no-undef` for `.ts`/`.tsx` in favor of `tsc --noEmit` (typescript-eslint's own documented recommendation, since ESLint's rule doesn't understand TypeScript's ambient types). Also added the missing `@types/jest-axe` dev dependency, without which `tsc --noEmit` failed.

## 4. Real content, not fabricated data

Nothing in this phase's own new code is placeholder: `AdminServiceProvider` reads a real manifest file produced by actually running `npx vite build` (verified the exact key/shape — `assets/react/main.tsx` → `{file, css}` — matches what the code reads), enqueues real hashed asset URLs, and localizes real REST config. The frontend it activates was already real (see §2). No mock production data, no fake WordPress content, anywhere in this phase.

## 5. A documented deviation from the docs

`docs/04-Folder-Structure.md` describes an elaborate `Admin/{Dashboard,Pages,Settings,Widgets,Components,Assets,Views,Controllers,Middleware}` breakdown implying per-page server-rendered PHP views — but the same document explicitly states (line 271) "There is no per-module Views/ or Assets/ folder. The admin UI is a single centralized React SPA." Since the SPA already owns its own navigation and rendering, only a single `AdminServiceProvider.php` was built; the elaborate per-page folder breakdown doesn't apply to what a single-mount SPA needs. Logged in `DECISIONS.md` with the same "aspirational vs. literal" precedent every prior phase has used for internally-contradictory doc sections.

## 6. Checks performed — all run for real

**PHP:** `composer validate` → valid. `composer test` → `OK (338 tests, 622 assertions)` — up from 329/612 at the end of Phase 11. `composer test:integration` → 0 tests (unchanged). PHPStan level 8 → `[OK] No errors` across 86 analysed files (up from 85). PHPCS (hybrid ruleset) → 0 errors, 0 warnings across 154 files. `composer quality` → all green.

**Frontend (run for the first time this phase — no prior `node_modules` existed):** `npm install` (failed once on the `eslint-plugin-react-hooks` peer conflict, fixed, succeeded). `npm run lint` → 0 problems (after the `no-undef` fix; 25 errors before). `npm run typecheck` → 0 errors (after adding `@types/jest-axe`; 5 errors before). `npm run test` → 4 suites / 6 tests passing, including the `jest-axe` a11y assertion on `DashboardPage`. `npm run build` → succeeded, confirmed producing `dist/.vite/manifest.json` in exactly the shape `AdminServiceProvider` expects. `npm audit` → 28 pre-existing advisories (1 moderate/27 high), entirely in dev-tooling transitive dependencies (not `dependencies`, which is only `react`/`react-dom`) — deliberately not force-fixed this phase (see DECISIONS.md); `package-lock.json` committed for reproducible installs.

## 7. Documentation updates

None to `docs/*`.

## 8. Decisions requiring your attention

Full detail in `.project/DECISIONS.md`. Most likely to need a second look:

- **Single-mount SPA over the doc's per-page `Admin/` folder breakdown** — a deliberate, doc-internal-contradiction-driven simplification, not an oversight.
- **Constructor-injected `FileRepository` on `AdminServiceProvider`** — the one Provider in this codebase with a constructor parameter beyond `Application`, needed to keep tests off real WordPress globals.
- **28 unfixed `npm audit` advisories** — all in dev-only tooling, deliberately deferred rather than force-upgraded blind.

## 9. Files created/modified this phase

New: `app/Admin/AdminServiceProvider.php`, `tests/Unit/Admin/AdminServiceProviderTest.php`. Modified: `app/Core/Plugin.php` (registers the new provider), `package.json` (`eslint-plugin-react-hooks` bump, `@types/jest-axe` added), `eslint.config.js` (`no-undef` off for TS). Landed as new, previously-uncommitted files: the entire `assets/react/**` SPA, `package-lock.json`, `vite.config.ts`, `tsconfig.json`, `tsconfig.jest.json`, `jest.config.cjs`, `tailwind.config.js`, `postcss.config.js`, `.prettierrc.json`. `.project/PROGRESS.md`, `.project/DECISIONS.md`, `.project/FILE-MANIFEST.md` updated, this report new.

## 10. What's explicitly still missing (by design — later phases)

Nav entries/screens for API Catalog, MCP, Agent Skills, Commerce, Logs, Settings, About (no REST backend yet — Phases 13-15); each module screen's full aspirational feature set (visual builders, multi-row config, per-post-type settings); dark mode toggle, global search, notifications/toast system; an HMR dev server (only `vite build --watch`); `Core/Scheduler.php`, any custom `oxy_*` database table or migration, Settings Manager, Logger service, Cache Service, Queue, Monitoring/Reporting engines, MCP/Agent Skills/API Catalog/OAuth Discovery modules.

## 11. Git

Committed as "Phase 12: Admin UI shell," tagged `phase-12`, pushed to `origin/main` along with the tag.

---

**Phase 12 complete. Per the user's standing autonomous-mode authorization, stopping here as instructed — the next phase (13) is not started.**
