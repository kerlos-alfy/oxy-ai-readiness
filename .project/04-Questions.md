# Open Questions

Questions for the user/product owner. Updated after Phase 0.5 — Architecture Normalization.

## Resolved in Phase 0.5

~~2. Canonical module folder structure~~ → **Resolved: ADR-002.**
~~3. Module vs Standard relationship~~ → **Resolved: ADR-001.**
~~4. Score/grade boundaries~~ → **Resolved: ADR-005.**
~~9. REST route prefixing~~ → **Resolved: ADR-003.**

## Still blocking (needed before Phase 1 code is written)

1. **Phase plan authority.** `docs/30-Claude-Code-Master.md` still contains no distinct phase breakdown (Conflict #1, out of scope for architecture normalization). Please review and approve (or amend) `.project/06-Phase-Plan.md` before Phase 1 begins.

## Important (needed before the affected phase, not before Phase 1)

5. **Licensing/editions scope.** `01-Vision.md` describes 5 commercial editions; `05-Modules.md` lists a License module; `25-Database-Schema.md` has license tables — but there is no dedicated License spec doc. Is License/entitlement enforcement in scope for the initial build, or should the License module be stubbed/deferred?
6. **Multisite scope.** Should Phase 1 architecture keep multisite in mind structurally (recommended) but defer actual multisite *feature* work to a later phase?
7. **Commerce / OAuth / Analytics modules.** Only brief mentions in `05-Modules.md`, no dedicated spec doc. Confirm these are lower-priority / later-phase modules.
8. **External integrations during early phases.** Confirm "Do not use mock production data" refers to test fixtures/content, not a prohibition on stubbing not-yet-built external services (license server, cloud sync) during early phases.

## Lower priority (can be answered as those phases approach)

10. **Target version pins.** Confirm exact minimum PHP/WP versions for `composer.json`/plugin header.
11. **Security score weight.** Confirm the 2% security weight in the Scoring Engine (ADR-005 left this unchanged as a product decision, not a doc conflict) is intentional.
