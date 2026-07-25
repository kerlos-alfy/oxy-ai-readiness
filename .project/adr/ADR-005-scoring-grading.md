# ADR-005: Unified Score / Grade / Label Scale

**Status:** Accepted
**Date:** 2026-07-24
**Resolves:** `.project/03-Conflicts.md` §4

## Context

Three non-aligned scales existed:

- `docs/06-Audit-Engine.md` "AI Readiness Score": 5 bands — 0–29 Poor, 30–49 Basic, 50–69 Good, 70–89 Advanced, 90–100 Excellent.
- `docs/15-Scoring-Engine.md` "GRADE SYSTEM": 10 letter-grade bands — A+ 98–100, A 95–97, A- 90–94, B+ 85–89, B 80–84, B- 75–79, C+ 70–74, C 60–69, D 40–59, F 0–39.
- `docs/15-Scoring-Engine.md` "AI READINESS LEVELS" (same document, different section): 6 bands — 0–20 Unusable, 21–40 Very Poor, 41–60 Basic, 61–80 Good, 81–90 Advanced, 91–100 Enterprise Ready.

None of the three boundary sets agree, and two conflicting label systems exist inside the same document.

## Decision

**The GRADE SYSTEM boundaries from `docs/15-Scoring-Engine.md` are canonical**, because `docs/28-Testing-Strategy.md`'s "GRADE BOUNDARY TESTS" section already hard-codes test assertions at exactly those boundary values (0, 39, 40, 59, 60, 69, 70, 74, 75, 79, 80, 84, 85, 89, 90, 94, 95, 97, 98, 100) — meaning the grade scale is the one the rest of the spec already treats as load-bearing.

`docs/06-Audit-Engine.md`'s 5 descriptive labels (Poor/Basic/Good/Advanced/Excellent) are kept — they read better in UI copy than letter grades alone — but are now derived from, not independent of, the grade boundaries. `docs/15-Scoring-Engine.md`'s separate "AI READINESS LEVELS" section (Unusable/Very Poor/.../Enterprise Ready) is **removed** as a redundant, non-agreeing duplicate.

### Canonical unified table

| Score | Grade | Label |
|---|---|---|
| 98–100 | A+ | Excellent |
| 95–97  | A  | Excellent |
| 90–94  | A- | Excellent |
| 85–89  | B+ | Advanced |
| 80–84  | B  | Advanced |
| 75–79  | B- | Advanced |
| 70–74  | C+ | Good |
| 60–69  | C  | Good |
| 40–59  | D  | Basic |
| 0–39   | F  | Poor |

Every score display (Dashboard AI Score card, Score Breakdown, Reports) shows the numeric score plus **one** grade **and** its associated label — never the old 5-band or 6-band boundaries independently.

Confidence Score (Low/Medium/High/Very High, per `docs/06-Audit-Engine.md` and `docs/15-Scoring-Engine.md`) is a **separate** metric measuring audit completeness/reliability, not overlapping with the AI Readiness Score/Grade — no conflict there, unchanged.

### Explicitly out of scope of this ADR

Category weighting (Discovery 20%, Content 20%, Infrastructure 15%, Headers 10%, Markdown 10%, LLMS 10%, MCP 5%, Agent Skills 5%, Performance 3%, Security 2%) is a **product tuning decision**, not a documentation conflict, and is left unchanged. (Flagged separately in `.project/03-Conflicts.md` §7 / `.project/04-Questions.md` Q11 as something to confirm with the user later — not resolved here.)

## Consequences

- `docs/15-Scoring-Engine.md`'s "AI READINESS LEVELS" section is replaced with the unified table above.
- `docs/06-Audit-Engine.md`'s "AI Readiness Score" section is updated to reference the same unified table instead of its own independent boundaries.
- `docs/28-Testing-Strategy.md`'s existing grade-boundary test list requires no changes — it already matches the now-canonical scale.
